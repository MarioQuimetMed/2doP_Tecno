<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destino extends Model
{
    use HasFactory;

    protected $fillable = [
        'pais',
        'ciudad',
        'nombre_lugar',
        'descripcion',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Un destino tiene muchos planes de viaje
     */
    public function planesViaje()
    {
        return $this->hasMany(PlanViaje::class);
    }

    // ===== SCOPES =====

    /**
     * Scope: Buscar por país
     */
    public function scopePorPais($query, $pais)
    {
        return $query->where('pais', 'ILIKE', "%{$pais}%");
    }

    /**
     * Scope: Buscar por ciudad
     */
    public function scopePorCiudad($query, $ciudad)
    {
        return $query->where('ciudad', 'ILIKE', "%{$ciudad}%");
    }

    /**
     * Scope: Buscar en nombre o descripción
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre_lugar', 'ILIKE', "%{$termino}%")
              ->orWhere('descripcion', 'ILIKE', "%{$termino}%")
              ->orWhere('pais', 'ILIKE', "%{$termino}%")
              ->orWhere('ciudad', 'ILIKE', "%{$termino}%");
        });
    }

    // ===== ACCESSORS =====

    /**
     * Obtener nombre completo del destino
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre_lugar}, {$this->ciudad}, {$this->pais}";
    }
}
