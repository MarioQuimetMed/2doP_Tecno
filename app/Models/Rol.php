<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rols';

    protected $fillable = [
        'nombre',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Un rol tiene muchos usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    // ===== SCOPES =====

    /**
     * Scope: Buscar por nombre
     */
    public function scopePorNombre($query, $nombre)
    {
        return $query->where('nombre', $nombre);
    }

    // ===== MÉTODOS ESTÁTICOS =====

    /**
     * Obtener ID del rol Administrador
     */
    public static function administrador()
    {
        return static::where('nombre', 'Administrador')->first();
    }

    /**
     * Obtener ID del rol Vendedor
     */
    public static function vendedor()
    {
        return static::where('nombre', 'Vendedor')->first();
    }

    /**
     * Obtener ID del rol Cliente
     */
    public static function cliente()
    {
        return static::where('nombre', 'Cliente')->first();
    }
}
