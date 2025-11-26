<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'accion',
        'tabla',
        'registro_id',
        'datos_anteriores',
        'datos_nuevos',
        'ip',
        'user_agent',
        'descripcion',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
    ];

    // ===== RELACIONES =====

    /**
     * Relación: Una entrada de bitácora pertenece a un usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ===== SCOPES =====

    /**
     * Scope: Filtrar por acción
     */
    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    /**
     * Scope: Filtrar por tabla
     */
    public function scopePorTabla($query, $tabla)
    {
        return $query->where('tabla', $tabla);
    }

    /**
     * Scope: Filtrar por usuario
     */
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Registros de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Por rango de fechas
     */
    public function scopePorRangoFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('created_at', [$desde, $hasta]);
    }

    // ===== MÉTODOS ESTÁTICOS =====

    /**
     * Registrar una acción en la bitácora
     */
    public static function registrar(
        string $accion,
        ?string $tabla = null,
        ?int $registroId = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        ?string $descripcion = null
    ): void {
        static::create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'tabla' => $tabla,
            'registro_id' => $registroId,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'descripcion' => $descripcion,
        ]);
    }

    /**
     * Registrar creación
     */
    public static function registrarCreacion(string $tabla, int $registroId, array $datos, ?string $descripcion = null): void
    {
        static::registrar('CREATE', $tabla, $registroId, null, $datos, $descripcion);
    }

    /**
     * Registrar actualización
     */
    public static function registrarActualizacion(string $tabla, int $registroId, array $datosAnteriores, array $datosNuevos, ?string $descripcion = null): void
    {
        static::registrar('UPDATE', $tabla, $registroId, $datosAnteriores, $datosNuevos, $descripcion);
    }

    /**
     * Registrar eliminación
     */
    public static function registrarEliminacion(string $tabla, int $registroId, array $datos, ?string $descripcion = null): void
    {
        static::registrar('DELETE', $tabla, $registroId, $datos, null, $descripcion);
    }

    /**
     * Registrar login
     */
    public static function registrarLogin(?string $descripcion = null): void
    {
        static::registrar('LOGIN', null, null, null, null, $descripcion ?? 'Usuario inició sesión');
    }

    /**
     * Registrar logout
     */
    public static function registrarLogout(?string $descripcion = null): void
    {
        static::registrar('LOGOUT', null, null, null, null, $descripcion ?? 'Usuario cerró sesión');
    }
}
