<?php

namespace App\Enums;

enum TeacherType: string
{
    case Principal = 'principal';
    case Auxiliary = 'auxiliary';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Principal->value => 'Principal',
            self::Auxiliary->value => 'Auxiliar',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
