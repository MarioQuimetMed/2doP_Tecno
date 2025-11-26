<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPago extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'cantidad_cuotas',
        'tasa_interes',
        'dia_vencimiento_mensual',
    ];

    protected $casts = [
        'cantidad_cuotas' => 'integer',
        'tasa_interes' => 'decimal:2',
        'dia_vencimiento_mensual' => 'integer',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Un plan de pago pertenece a una venta (1:1)
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * Relación: Un plan de pago tiene muchas cuotas
     */
    public function cuotas()
    {
        return $this->hasMany(Cuota::class)->orderBy('numero_cuota');
    }

    // ===== MÉTODOS =====

    /**
     * Generar cuotas automáticamente
     */
    public function generarCuotas(): void
    {
        $montoTotal = $this->venta->monto_total;
        $tasaMensual = $this->tasa_interes / 100;
        
        // Calcular cuota mensual con interés compuesto
        if ($tasaMensual > 0) {
            $cuotaMensual = $montoTotal * (
                ($tasaMensual * pow(1 + $tasaMensual, $this->cantidad_cuotas)) /
                (pow(1 + $tasaMensual, $this->cantidad_cuotas) - 1)
            );
        } else {
            $cuotaMensual = $montoTotal / $this->cantidad_cuotas;
        }
        
        $saldoCapital = $montoTotal;
        $fechaBase = $this->venta->fecha_venta;
        
        for ($i = 1; $i <= $this->cantidad_cuotas; $i++) {
            // Calcular fecha de vencimiento
            $fechaVencimiento = $fechaBase->copy()
                ->addMonths($i)
                ->day($this->dia_vencimiento_mensual);
            
            // Calcular interés sobre saldo
            $montoInteres = $saldoCapital * $tasaMensual;
            $montoCapital = $cuotaMensual - $montoInteres;
            
            // Ajustar última cuota para cubrir diferencias de redondeo
            if ($i === $this->cantidad_cuotas) {
                $montoCapital = $saldoCapital;
                $cuotaMensual = $montoCapital + $montoInteres;
            }
            
            Cuota::create([
                'plan_pago_id' => $this->id,
                'numero_cuota' => $i,
                'fecha_vencimiento' => $fechaVencimiento,
                'monto_capital' => round($montoCapital, 2),
                'monto_interes' => round($montoInteres, 2),
                'monto_total_cuota' => round($cuotaMensual, 2),
            ]);
            
            $saldoCapital -= $montoCapital;
        }
    }

    /**
     * Calcular monto total con intereses
     */
    public function montoTotalConIntereses(): float
    {
        return $this->cuotas()->sum('monto_total_cuota');
    }

    /**
     * Calcular total de intereses
     */
    public function totalIntereses(): float
    {
        return $this->cuotas()->sum('monto_interes');
    }

    // ===== ACCESSORS =====

    /**
     * Obtener tasa de interés formateada
     */
    public function getTasaInteresFormateadaAttribute()
    {
        return $this->tasa_interes . '%';
    }
}
