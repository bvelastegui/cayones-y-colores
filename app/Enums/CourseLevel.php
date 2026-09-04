<?php

namespace App\Enums;

enum CourseLevel: string
{
    case Maternal1 = 'maternal_1';
    case Maternal2 = 'maternal_2';
    case Initial1 = 'initial_1';
    case Initial2 = 'initial_2';
    case FirstBasic = 'first_basic';

    /**
     * Maximum students allowed per parallel before an auxiliary is required.
     */
    public function maxCapacity(): ?int
    {
        return match ($this) {
            self::FirstBasic => 9,
            default => null,
        };
    }

    /**
     * Number of students per required auxiliary teacher.
     */
    public function studentsPerAuxiliary(): ?int
    {
        return match ($this) {
            self::Maternal1, self::Maternal2 => 3,
            self::Initial1, self::Initial2 => 6,
            default => null,
        };
    }

    /**
     * Minimum enrollment percentage required to open a new parallel.
     */
    public function minimumEnrollmentPercentage(): ?int
    {
        return match ($this) {
            self::FirstBasic => 60,
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Maternal1->value => 'Maternal 1',
            self::Maternal2->value => 'Maternal 2',
            self::Initial1->value => 'Inicial 1',
            self::Initial2->value => 'Inicial 2',
            self::FirstBasic->value => 'Primero EGB',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
