<?php

namespace App\Enums;

enum TuitionConcept: string
{
    case Enrollment = 'enrollment';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::Enrollment => 'Matrícula',
            self::Monthly => 'Pensión',
        };
    }
}
