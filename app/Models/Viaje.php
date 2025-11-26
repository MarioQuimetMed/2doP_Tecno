<?php

namespace App\Models;

use App\Enums\EstadoViaje;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viaje extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_viaje_id',
        'fecha_salida',
        'fecha_retorno',
        'cupos_totales',
        'cupos_disponibles',
        'estado_viaje',
    ];

    protected $casts = [
        'fecha_salida' => 'date',
        'fecha_retorno' => 'date',
        'cupos_totales' => 'integer',
        'cupos_disponibles' => 'integer',
        'estado_viaje' => EstadoViaje::class,
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Un viaje pertenece a un plan
     */
    public function planViaje()
    {
        return $this->belongsTo(PlanViaje::class);
    }

    /**
     * Relación: Un viaje tiene muchas ventas
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    // ===== SCOPES =====

    /**
     * Scope: Viajes disponibles para venta
     */
    public function scopeDisponibles($query)
    {
        return $query->whereIn('estado_viaje', [
            EstadoViaje::ABIERTO->value,
            EstadoViaje::LLENO->value,
        ])->where('cupos_disponibles', '>', 0);
    }

    /**
     * Scope: Viajes próximos
     */
    public function scopeProximos($query)
    {
        return $query->where('fecha_salida', '>=', now())
                     ->orderBy('fecha_salida');
    }

    /**
     * Scope: Viajes en curso
     */
    public function scopeEnCurso($query)
    {
        return $query->where('estado_viaje', EstadoViaje::EN_CURSO->value);
    }

    /**
     * Scope: Filtrar por estado
     */
    public function scopePorEstado($query, EstadoViaje $estado)
    {
        return $query->where('estado_viaje', $estado->value);
    }

    // ===== MÉTODOS =====

    /**
     * Reservar cupos
     */
    public function reservarCupos(int $cantidad): bool
    {
        if ($this->cupos_disponibles >= $cantidad) {
            $this->cupos_disponibles -= $cantidad;
            
            // Actualizar estado si se llenó
            if ($this->cupos_disponibles === 0) {
                $this->estado_viaje = EstadoViaje::LLENO;
            }
            
            return $this->save();
        }
        
        return false;
    }

    /**
     * Liberar cupos (por cancelación)
     */
    public function liberarCupos(int $cantidad): bool
    {
        $this->cupos_disponibles += $cantidad;
        
        // Si estaba lleno, volver a abierto
        if ($this->estado_viaje === EstadoViaje::LLENO) {
            $this->estado_viaje = EstadoViaje::ABIERTO;
        }
        
        return $this->save();
    }

    /**
     * Verificar si tiene cupos disponibles
     */
    public function tieneCuposDisponibles(int $cantidad = 1): bool
    {
        return $this->cupos_disponibles >= $cantidad;
    }

    // ===== ACCESSORS =====

    /**
     * Obtener cupos vendidos
     */
    public function getCuposVendidosAttribute()
    {
        return $this->cupos_totales - $this->cupos_disponibles;
    }

    /**
     * Obtener porcentaje de ocupación
     */
    public function getPorcentajeOcupacionAttribute()
    {
        if ($this->cupos_totales === 0) return 0;
        return round(($this->cupos_vendidos / $this->cupos_totales) * 100, 2);
    }
}
