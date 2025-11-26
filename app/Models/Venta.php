<?php

namespace App\Models;

use App\Enums\EstadoPago;
use App\Enums\TipoPago;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'vendedor_id',
        'viaje_id',
        'fecha_venta',
        'monto_total',
        'tipo_pago',
        'estado_pago',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'monto_total' => 'decimal:2',
        'tipo_pago' => TipoPago::class,
        'estado_pago' => EstadoPago::class,
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Una venta pertenece a un cliente
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * Relación: Una venta pertenece a un vendedor
     */
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    /**
     * Relación: Una venta pertenece a un viaje
     */
    public function viaje()
    {
        return $this->belongsTo(Viaje::class);
    }

    /**
     * Relación: Una venta tiene un plan de pago (1:1)
     */
    public function planPago()
    {
        return $this->hasOne(PlanPago::class);
    }

    /**
     * Relación: Una venta tiene muchos pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    // ===== SCOPES =====

    /**
     * Scope: Ventas al contado
     */
    public function scopeAlContado($query)
    {
        return $query->where('tipo_pago', TipoPago::CONTADO->value);
    }

    /**
     * Scope: Ventas a crédito
     */
    public function scopeACredito($query)
    {
        return $query->where('tipo_pago', TipoPago::CREDITO->value);
    }

    /**
     * Scope: Ventas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado_pago', EstadoPago::PENDIENTE->value);
    }

    /**
     * Scope: Ventas completadas
     */
    public function scopeCompletadas($query)
    {
        return $query->where('estado_pago', EstadoPago::COMPLETADO->value);
    }

    /**
     * Scope: Filtrar por rango de fechas
     */
    public function scopePorRangoFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_venta', [$fechaInicio, $fechaFin]);
    }

    // ===== MÉTODOS =====

    /**
     * Calcular monto pagado hasta ahora
     */
    public function montoPagado(): float
    {
        return $this->pagos()->sum('monto_pagado');
    }

    /**
     * Calcular saldo pendiente
     */
    public function saldoPendiente(): float
    {
        return $this->monto_total - $this->montoPagado();
    }

    /**
     * Actualizar estado de pago según pagos recibidos
     */
    public function actualizarEstadoPago(): void
    {
        $montoPagado = $this->montoPagado();
        
        if ($montoPagado >= $this->monto_total) {
            $this->estado_pago = EstadoPago::COMPLETADO;
        } elseif ($montoPagado > 0) {
            $this->estado_pago = EstadoPago::PARCIAL;
        } else {
            $this->estado_pago = EstadoPago::PENDIENTE;
        }
        
        $this->save();
    }

    /**
     * Verificar si es venta a crédito
     */
    public function esACredito(): bool
    {
        return $this->tipo_pago === TipoPago::CREDITO;
    }

    /**
     * Verificar si está completamente pagada
     */
    public function estaPagada(): bool
    {
        return $this->estado_pago === EstadoPago::COMPLETADO;
    }

    // ===== ACCESSORS =====

    /**
     * Obtener monto formateado
     */
    public function getMontoFormateadoAttribute()
    {
        return '$' . number_format($this->monto_total, 2);
    }

    /**
     * Obtener porcentaje pagado
     */
    public function getPorcentajePagadoAttribute()
    {
        if ($this->monto_total == 0) return 0;
        return round(($this->montoPagado() / $this->monto_total) * 100, 2);
    }
}
