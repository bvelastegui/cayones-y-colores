<?php

namespace App\Enums;

enum AdmissionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Pending->value => 'Pendiente',
            self::Approved->value => 'Aprobada',
            self::Rejected->value => 'Rechazada',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
