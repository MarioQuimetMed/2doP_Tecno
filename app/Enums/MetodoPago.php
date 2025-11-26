<?php

namespace App\Enums;

enum MetodoPago: string
{
    case EFECTIVO = 'EFECTIVO';
    case TRANSFERENCIA = 'TRANSFERENCIA';
    case QR = 'QR';
    case TARJETA = 'TARJETA';

    public function label(): string
    {
        return match($this) {
            self::EFECTIVO => 'Efectivo',
            self::TRANSFERENCIA => 'Transferencia Bancaria',
            self::QR => 'Código QR',
            self::TARJETA => 'Tarjeta de Crédito/Débito',
        };
    }

    public function requiereReferencia(): bool
    {
        return in_array($this, [self::TRANSFERENCIA, self::QR, self::TARJETA]);
    }
}
