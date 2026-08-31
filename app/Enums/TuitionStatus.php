<?php

namespace App\Enums;

enum TuitionStatus: string
{
    case Pending = 'pending';
    case Partial = 'partial';
    case Paid = 'paid';
    case Overdue = 'overdue';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Pending->value => 'Pendiente',
            self::Partial->value => 'Parcial',
            self::Paid->value => 'Pagada',
            self::Overdue->value => 'Vencida',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
