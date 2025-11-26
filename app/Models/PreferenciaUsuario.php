<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreferenciaUsuario extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tema_id',
        'tamaño_fuente',
        'alto_contraste',
        'modo_oscuro_auto',
    ];

    protected $casts = [
        'alto_contraste' => 'boolean',
        'modo_oscuro_auto' => 'boolean',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Una preferencia pertenece a un usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Una preferencia usa un tema
     */
    public function tema()
    {
        return $this->belongsTo(Tema::class);
    }

    // ===== MÉTODOS =====

    /**
     * Determinar si debe usar modo oscuro según la hora
     */
    public function debeUsarModoOscuro(): bool
    {
        if (!$this->modo_oscuro_auto) {
            return false;
        }

        $hora = now()->hour;
        // Modo oscuro entre 18:00 y 06:00
        return $hora >= 18 || $hora < 6;
    }

    /**
     * Obtener el multiplicador de tamaño de fuente
     */
    public function getMultiplicadorFuente(): float
    {
        return match($this->tamaño_fuente) {
            'pequeño' => 0.875,
            'normal' => 1.0,
            'grande' => 1.125,
            'extra_grande' => 1.25,
            default => 1.0,
        };
    }
}
