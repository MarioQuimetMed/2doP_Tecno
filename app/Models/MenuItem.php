<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'rol_id',
        'titulo',
        'ruta',
        'icono',
        'orden',
        'activo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Un item pertenece a un menú
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Relación: Un item puede tener un padre
     */
    public function padre()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Relación: Un item puede tener hijos
     */
    public function hijos()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('orden');
    }

    /**
     * Relación: Un item puede estar restringido a un rol
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    // ===== SCOPES =====

    /**
     * Scope: Solo items activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Items de nivel superior
     */
    public function scopeRaiz($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Filtrar por rol del usuario
     */
    public function scopeParaRol($query, $rolId)
    {
        return $query->where(function ($q) use ($rolId) {
            $q->whereNull('rol_id')
              ->orWhere('rol_id', $rolId);
        });
    }

    // ===== MÉTODOS =====

    /**
     * Verificar si el item tiene hijos
     */
    public function tieneHijos(): bool
    {
        return $this->hijos()->count() > 0;
    }
}
