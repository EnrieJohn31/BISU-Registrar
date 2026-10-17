<?php

namespace App\Support\Clients;

/**
 * Lists show masked identifiers; the detail page shows the full value to staff
 * who may view client records.
 */
final class IdentifierMask
{
    public static function mask(string $value): string
    {
        $length = mb_strlen($value);

        if ($length <= 4) {
            return str_repeat('•', $length);
        }

        return str_repeat('•', $length - 4).mb_substr($value, -4);
    }
}
