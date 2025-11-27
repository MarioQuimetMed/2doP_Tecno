<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanViaje extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'destino_id',
        'nombre',
        'descripcion',
        'duracion_dias',
        'precio_base',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'duracion_dias' => 'integer',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Un plan pertenece a un destino
     */
    public function destino()
    {
        return $this->belongsTo(Destino::class);
    }

    /**
     * Relación: Un plan tiene muchas actividades diarias
     */
    public function actividadesDiarias()
    {
        return $this->hasMany(ActividadDiaria::class)->orderBy('dia_numero');
    }

    /**
     * Relación: Un plan tiene muchos viajes programados
     */
    public function viajes()
    {
        return $this->hasMany(Viaje::class);
    }

    // ===== SCOPES =====

    /**
     * Scope: Buscar por nombre o descripción
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'ILIKE', "%{$termino}%")
              ->orWhere('descripcion', 'ILIKE', "%{$termino}%");
        });
    }

    /**
     * Scope: Filtrar por rango de precio
     */
    public function scopePorRangoPrecio($query, $min, $max)
    {
        return $query->whereBetween('precio_base', [$min, $max]);
    }

    /**
     * Scope: Filtrar por duración
     */
    public function scopePorDuracion($query, $dias)
    {
        return $query->where('duracion_dias', $dias);
    }

    // ===== ACCESSORS =====

    /**
     * Obtener precio formateado
     */
    public function getPrecioFormateadoAttribute()
    {
        return '$' . number_format($this->precio_base, 2);
    }

    /**
     * Obtener duración en texto
     */
    public function getDuracionTextoAttribute()
    {
        return $this->duracion_dias . ($this->duracion_dias === 1 ? ' día' : ' días');
    }
}
