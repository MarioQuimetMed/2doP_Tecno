<?php

namespace App\Enums;

enum TipoPago: string
{
    case CONTADO = 'CONTADO';
    case CREDITO = 'CREDITO';

    public function label(): string
    {
        return match($this) {
            self::CONTADO => 'Pago al Contado',
            self::CREDITO => 'Pago a Crédito',
        };
    }
}
