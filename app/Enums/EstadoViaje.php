<?php

namespace App\Enums;

enum EstadoViaje: string
{
    case ABIERTO = 'ABIERTO';
    case LLENO = 'LLENO';
    case EN_CURSO = 'EN_CURSO';
    case FINALIZADO = 'FINALIZADO';
    case CANCELADO = 'CANCELADO';

    public function label(): string
    {
        return match($this) {
            self::ABIERTO => 'Abierto',
            self::LLENO => 'Lleno',
            self::EN_CURSO => 'En Curso',
            self::FINALIZADO => 'Finalizado',
            self::CANCELADO => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ABIERTO => 'green',
            self::LLENO => 'blue',
            self::EN_CURSO => 'purple',
            self::FINALIZADO => 'gray',
            self::CANCELADO => 'red',
        };
    }

    public function puedeVender(): bool
    {
        return in_array($this, [self::ABIERTO, self::LLENO]);
    }
}
