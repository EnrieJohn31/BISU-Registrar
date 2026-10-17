<?php

namespace App\Enums;

/**
 * Descriptive only: a category never decides eligibility or establishes
 * enrolment or graduation (System_Plan Part 04).
 */
enum ClientCategory: string
{
    case Student = 'student';
    case Alumni = 'alumni';
    case Both = 'both';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Student',
            self::Alumni => 'Alumni',
            self::Both => 'Student and alumni',
            self::Unknown => 'Not stated',
        };
    }
}
