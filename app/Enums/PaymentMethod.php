<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Payphone = 'payphone';
    case Cash = 'cash';
    case CreditCard = 'credit_card';
    case Transfer = 'transfer';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Payphone->value => 'Payphone',
            self::Cash->value => 'Efectivo',
            self::CreditCard->value => 'Tarjeta de crédito',
            self::Transfer->value => 'Transferencia',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
