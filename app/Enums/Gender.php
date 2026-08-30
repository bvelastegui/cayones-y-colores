<?php

namespace App\Enums;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Male->value => 'Masculino',
            self::Female->value => 'Femenino',
            self::Other->value => 'Otro',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
