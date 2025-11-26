<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tema extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'css_variables',
        'tipo',
        'activo',
    ];

    protected $casts = [
        'css_variables' => 'array',
        'activo' => 'boolean',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Un tema puede ser usado por muchos usuarios
     */
    public function preferencias()
    {
        return $this->hasMany(PreferenciaUsuario::class);
    }

    // ===== SCOPES =====

    /**
     * Scope: Solo temas activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Filtrar por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // ===== MÉTODOS ESTÁTICOS =====

    /**
     * Obtener tema para niños
     */
    public static function paraNinos()
    {
        return static::where('tipo', 'ninos')->where('activo', true)->first();
    }

    /**
     * Obtener tema para jóvenes
     */
    public static function paraJovenes()
    {
        return static::where('tipo', 'jovenes')->where('activo', true)->first();
    }

    /**
     * Obtener tema para adultos
     */
    public static function paraAdultos()
    {
        return static::where('tipo', 'adultos')->where('activo', true)->first();
    }
}
