<?php

namespace App\Enums;

enum EstadoPago: string
{
    case PENDIENTE = 'PENDIENTE';
    case PARCIAL = 'PARCIAL';
    case COMPLETADO = 'COMPLETADO';
    case ANULADO = 'ANULADO';

    public function label(): string
    {
        return match($this) {
            self::PENDIENTE => 'Pendiente',
            self::PARCIAL => 'Pago Parcial',
            self::COMPLETADO => 'Completado',
            self::ANULADO => 'Anulado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDIENTE => 'yellow',
            self::PARCIAL => 'blue',
            self::COMPLETADO => 'green',
            self::ANULADO => 'red',
        };
    }
}
