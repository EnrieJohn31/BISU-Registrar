<?php

namespace App\Support\Accounts;

use Carbon\CarbonImmutable;

/**
 * Temporary passwords are read aloud or printed and handed over in person, so
 * they avoid look-alike characters (0/O, 1/l/I).
 */
final class TemporaryPassword
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';

    public static function generate(): string
    {
        $groups = [];

        for ($group = 0; $group < 3; $group++) {
            $characters = '';

            for ($i = 0; $i < 4; $i++) {
                $characters .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }

            $groups[] = $characters;
        }

        return implode('-', $groups);
    }

    public static function expiresAt(): CarbonImmutable
    {
        $hours = config('bisu.temporary_password_hours');

        return CarbonImmutable::now()->addHours(is_numeric($hours) ? (int) $hours : 72);
    }
}
