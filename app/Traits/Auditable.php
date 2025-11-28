<?php

namespace App\Traits;

use App\Models\Bitacora;

/**
 * Trait Auditable
 * 
 * Registra automáticamente las operaciones CRUD en la bitácora.
 * Se aplica a los modelos que necesitan auditoría.
 */
trait Auditable
{
    /**
     * Boot del trait para registrar los eventos del modelo.
     */
    protected static function bootAuditable(): void
    {
        // Registrar cuando se crea un nuevo registro
        static::created(function ($model) {
            Bitacora::registrarCreacion(
                $model->getTable(),
                $model->getKey(),
                $model->getAttributes(),
                static::formatearDescripcion('crear', $model)
            );
        });

        // Registrar cuando se actualiza un registro
        static::updated(function ($model) {
            // Solo registrar si hay cambios reales (no solo updated_at)
            $cambios = $model->getChanges();
            unset($cambios['updated_at']);
            
            if (!empty($cambios)) {
                Bitacora::registrarActualizacion(
                    $model->getTable(),
                    $model->getKey(),
                    $model->getOriginal(),
                    $model->getAttributes(),
                    static::formatearDescripcion('actualizar', $model, $cambios)
                );
            }
        });

        // Registrar cuando se elimina un registro
        static::deleted(function ($model) {
            Bitacora::registrarEliminacion(
                $model->getTable(),
                $model->getKey(),
                $model->getAttributes(),
                static::formatearDescripcion('eliminar', $model)
            );
        });
    }

    /**
     * Formatea la descripción para la bitácora.
     */
    protected static function formatearDescripcion(string $accion, $model, array $cambios = []): string
    {
        $nombreModelo = class_basename($model);
        $identificador = static::obtenerIdentificador($model);

        switch ($accion) {
            case 'crear':
                return "Se creó {$nombreModelo}: {$identificador}";
            case 'actualizar':
                $camposModificados = implode(', ', array_keys($cambios));
                return "Se actualizó {$nombreModelo}: {$identificador}. Campos: {$camposModificados}";
            case 'eliminar':
                return "Se eliminó {$nombreModelo}: {$identificador}";
            default:
                return "Acción {$accion} en {$nombreModelo}: {$identificador}";
        }
    }

    /**
     * Obtiene un identificador legible del modelo.
     */
    protected static function obtenerIdentificador($model): string
    {
        // Intentar obtener un nombre o título del modelo
        $posiblesAtributos = ['nombre', 'name', 'titulo', 'title', 'descripcion', 'email'];
        
        foreach ($posiblesAtributos as $atributo) {
            if (isset($model->{$atributo}) && !empty($model->{$atributo})) {
                return "{$model->{$atributo}} (ID: {$model->getKey()})";
            }
        }

        return "ID: {$model->getKey()}";
    }

    /**
     * Registra una acción personalizada en la bitácora.
     */
    public function registrarAccionPersonalizada(string $accion, ?string $descripcion = null): void
    {
        Bitacora::registrar(
            strtoupper($accion),
            $this->getTable(),
            $this->getKey(),
            null,
            ['descripcion' => $descripcion ?? "Acción {$accion} realizada"]
        );
    }
}
