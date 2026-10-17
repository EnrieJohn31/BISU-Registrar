<?php

namespace App\Enums;

enum IdentifierType: string
{
    case Student = 'student';
    case Alumni = 'alumni';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Student ID',
            self::Alumni => 'Alumni ID',
        };
    }
}
