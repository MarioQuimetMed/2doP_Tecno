<?php

namespace App\Models;

use App\Enums\MetodoPago;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'venta_id',
        'cuota_id',
        'fecha_pago',
        'monto_pagado',
        'metodo_pago',
        'referencia_comprobante',
        'pagofacil_transaction_id',
        'company_transaction_id',
        'qr_base64',
        'checkout_url',
        'deep_link',
        'qr_content_url',
        'universal_url',
        'qr_expiration_date',
        'payment_status',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto_pagado' => 'decimal:2',
        'metodo_pago' => MetodoPago::class,
        'qr_expiration_date' => 'datetime',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Un pago pertenece a una venta
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * Relación: Un pago puede pertenecer a una cuota específica
     */
    public function cuota()
    {
        return $this->belongsTo(Cuota::class);
    }

    // ===== SCOPES =====

    /**
     * Scope: Pagos de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_pago', today());
    }

    /**
     * Scope: Pagos por rango de fechas
     */
    public function scopePorRangoFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope: Pagos por método
     */
    public function scopePorMetodo($query, MetodoPago $metodo)
    {
        return $query->where('metodo_pago', $metodo->value);
    }

    /**
     * Scope: Pagos en efectivo
     */
    public function scopeEnEfectivo($query)
    {
        return $query->where('metodo_pago', MetodoPago::EFECTIVO->value);
    }

    // ===== MÉTODOS =====

    /**
     * Verificar si es pago de cuota específica
     */
    public function esPagoDeCuota(): bool
    {
        return $this->cuota_id !== null;
    }

    /**
     * Verificar si es pago general (adelanto)
     */
    public function esPagoGeneral(): bool
    {
        return $this->cuota_id === null;
    }

    // ===== ACCESSORS =====

    /**
     * Obtener monto formateado
     */
    public function getMontoFormateadoAttribute()
    {
        return '$' . number_format($this->monto_pagado, 2);
    }

    /**
     * Obtener descripción del pago
     */
    public function getDescripcionAttribute()
    {
        if ($this->esPagoDeCuota()) {
            return "Pago de cuota #{$this->cuota->numero_cuota}";
        }
        return "Pago general / Adelanto";
    }

    // ===== EVENTS =====

    protected static function booted()
    {
        // Después de crear un pago, actualizar estado de venta y cuota
        static::created(function ($pago) {
            // Actualizar estado de la venta
            $pago->venta->actualizarEstadoPago();
            
            // Si es pago de cuota, verificar si la cuota está pagada
            if ($pago->cuota_id) {
                $cuota = $pago->cuota;
                if ($cuota->estaPagada()) {
                    $cuota->marcarComoPagada();
                }
            }
        });

        // Cuando se actualiza un pago (ej: callback de PagoFácil)
        static::updated(function ($pago) {
            // Si el estado cambió a COMPLETADO
            if ($pago->isDirty('payment_status') && $pago->payment_status === 'COMPLETED') {
                // Actualizar estado de la venta
                $pago->venta->actualizarEstadoPago();
                
                // Si es pago de cuota, verificar si la cuota está pagada
                if ($pago->cuota_id) {
                    $cuota = $pago->cuota;
                    // Recalcular si está pagada (ahora montoPagado filtra por COMPLETED)
                    if ($cuota->estaPagada()) {
                        $cuota->marcarComoPagada();
                    }
                }
            }
        });
    }
}
