<?php

namespace App\Enums;

enum EstadoCuota: string
{
    case PENDIENTE = 'PENDIENTE';
    case PAGADO = 'PAGADO';
    case VENCIDO = 'VENCIDO';

    public function label(): string
    {
        return match($this) {
            self::PENDIENTE => 'Pendiente',
            self::PAGADO => 'Pagado',
            self::VENCIDO => 'Vencido',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDIENTE => 'yellow',
            self::PAGADO => 'green',
            self::VENCIDO => 'red',
        };
    }
}
