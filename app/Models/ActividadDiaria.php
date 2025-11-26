<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadDiaria extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_viaje_id',
        'dia_numero',
        'descripcion_actividad',
        'hora_inicio',
    ];

    protected $casts = [
        'dia_numero' => 'integer',
        'hora_inicio' => 'datetime:H:i',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Una actividad pertenece a un plan de viaje
     */
    public function planViaje()
    {
        return $this->belongsTo(PlanViaje::class);
    }

    // ===== SCOPES =====

    /**
     * Scope: Ordenar por día
     */
    public function scopeOrdenadoPorDia($query)
    {
        return $query->orderBy('dia_numero')->orderBy('hora_inicio');
    }

    /**
     * Scope: Filtrar por día específico
     */
    public function scopePorDia($query, $dia)
    {
        return $query->where('dia_numero', $dia);
    }

    // ===== ACCESSORS =====

    /**
     * Obtener título del día
     */
    public function getTituloDiaAttribute()
    {
        return "Día {$this->dia_numero}";
    }
}
