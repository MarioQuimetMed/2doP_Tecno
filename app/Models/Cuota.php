<?php

namespace App\Models;

use App\Enums\EstadoCuota;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cuota extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'plan_pago_id',
        'numero_cuota',
        'fecha_vencimiento',
        'monto_capital',
        'monto_interes',
        'monto_total_cuota',
        'estado_cuota',
    ];

    protected $casts = [
        'numero_cuota' => 'integer',
        'fecha_vencimiento' => 'date',
        'monto_capital' => 'decimal:2',
        'monto_interes' => 'decimal:2',
        'monto_total_cuota' => 'decimal:2',
        'estado_cuota' => EstadoCuota::class,
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Una cuota pertenece a un plan de pago
     */
    public function planPago()
    {
        return $this->belongsTo(PlanPago::class);
    }

    /**
     * Relación: Una cuota tiene muchos pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    // ===== SCOPES =====

    /**
     * Scope: Cuotas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado_cuota', EstadoCuota::PENDIENTE->value);
    }

    /**
     * Scope: Cuotas pagadas
     */
    public function scopePagadas($query)
    {
        return $query->where('estado_cuota', EstadoCuota::PAGADO->value);
    }

    /**
     * Scope: Cuotas vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('estado_cuota', EstadoCuota::VENCIDO->value)
                     ->orWhere(function ($q) {
                         $q->where('estado_cuota', EstadoCuota::PENDIENTE->value)
                           ->where('fecha_vencimiento', '<', now());
                     });
    }

    /**
     * Scope: Cuotas próximas a vencer
     */
    public function scopeProximasAVencer($query, $dias = 7)
    {
        return $query->where('estado_cuota', EstadoCuota::PENDIENTE->value)
                     ->whereBetween('fecha_vencimiento', [
                         now(),
                         now()->addDays($dias)
                     ]);
    }

    // ===== MÉTODOS =====

    /**
     * Verificar si está vencida
     */
    public function estaVencida(): bool
    {
        return $this->estado_cuota === EstadoCuota::PENDIENTE 
               && $this->fecha_vencimiento->isPast();
    }

    /**
     * Marcar como vencida
     */
    public function marcarComoVencida(): void
    {
        if ($this->estaVencida()) {
            $this->estado_cuota = EstadoCuota::VENCIDO;
            $this->save();
        }
    }

    /**
     * Marcar como pagada
     */
    public function marcarComoPagada(): void
    {
        $this->estado_cuota = EstadoCuota::PAGADO;
        $this->save();
    }

    /**
     * Calcular monto pagado de esta cuota
     */
    public function montoPagado(): float
    {
        return $this->pagos()
            ->where('payment_status', 'COMPLETED')
            ->sum('monto_pagado');
    }

    /**
     * Calcular saldo pendiente de esta cuota
     */
    public function saldoPendiente(): float
    {
        return $this->monto_total_cuota - $this->montoPagado();
    }

    /**
     * Verificar si está completamente pagada
     */
    public function estaPagada(): bool
    {
        return $this->montoPagado() >= $this->monto_total_cuota;
    }

    // ===== ACCESSORS =====

    /**
     * Obtener días hasta vencimiento
     */
    public function getDiasHastaVencimientoAttribute()
    {
        return now()->diffInDays($this->fecha_vencimiento, false);
    }

    /**
     * Obtener si está próxima a vencer
     */
    public function getProximaAVencerAttribute()
    {
        return $this->dias_hasta_vencimiento >= 0 
               && $this->dias_hasta_vencimiento <= 7
               && $this->estado_cuota === EstadoCuota::PENDIENTE;
    }

    /**
     * Obtener monto formateado
     */
    public function getMontoFormateadoAttribute()
    {
        return '$' . number_format($this->monto_total_cuota, 2);
    }
}
