<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Active = 'active';
    case Withdrawn = 'withdrawn';
    case Graduated = 'graduated';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Active->value => 'Activa',
            self::Withdrawn->value => 'Retirada',
            self::Graduated->value => 'Graduada',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
