<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Representative = 'representative';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Admin->value => 'Administrador',
            self::Teacher->value => 'Docente',
            self::Representative->value => 'Representante',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
