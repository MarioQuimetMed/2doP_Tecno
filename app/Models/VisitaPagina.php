<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitaPagina extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruta',
        'ip',
        'user_id',
        'user_agent',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Una visita puede pertenecer a un usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ===== SCOPES =====

    /**
     * Scope: Visitas de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Visitas por ruta
     */
    public function scopePorRuta($query, $ruta)
    {
        return $query->where('ruta', $ruta);
    }

    /**
     * Scope: Visitas únicas (por IP)
     */
    public function scopeUnicas($query)
    {
        return $query->distinct('ip');
    }

    // ===== MÉTODOS ESTÁTICOS =====

    /**
     * Contar visitas de una ruta
     */
    public static function contarVisitas($ruta): int
    {
        return static::where('ruta', $ruta)->count();
    }

    /**
     * Contar visitas únicas de una ruta
     */
    public static function contarVisitasUnicas($ruta): int
    {
        return static::where('ruta', $ruta)->distinct('ip')->count('ip');
    }

    /**
     * Registrar una visita
     */
    public static function registrar($ruta, $ip, $userId = null, $userAgent = null): void
    {
        static::create([
            'ruta' => $ruta,
            'ip' => $ip,
            'user_id' => $userId,
            'user_agent' => $userAgent,
        ]);
    }
}
