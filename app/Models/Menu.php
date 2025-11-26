<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Un menú tiene muchos items
     */
    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('orden');
    }

    /**
     * Relación: Items de nivel superior (sin padre)
     */
    public function itemsRaiz()
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('orden');
    }

    // ===== SCOPES =====

    /**
     * Scope: Solo menús activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
