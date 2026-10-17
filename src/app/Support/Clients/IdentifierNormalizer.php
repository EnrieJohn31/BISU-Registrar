<?php

namespace App\Support\Clients;

use Normalizer;

/**
 * DEMO identifier rule, version 1.
 *
 * BISU's real Student/Alumni ID formats are not known yet (decision D04), so
 * this rule is a labelled placeholder for development: it must not be read as
 * the campus format. Changing the rule needs approval and a versioned backfill;
 * the version is stored with every identifier.
 *
 *  - Unicode compatibility normalization, then surrounding whitespace removed.
 *  - 3 to 32 characters: ASCII letters, digits and hyphens, starting and ending
 *    with a letter or digit. No internal spaces.
 *  - Compared case-insensitively by storing the upper-case canonical form.
 *  - Always a string, so leading zeroes are preserved.
 */
final class IdentifierNormalizer
{
    public const VERSION = 1;

    public const DESCRIPTION = '3 to 32 letters, digits or hyphens, with no spaces (demo rule, pending Registrar confirmation).';

    /**
     * Value to display as entered (trimmed), or null when it breaks the rule.
     */
    public static function display(string $value): ?string
    {
        $value = trim(self::compatible($value));

        return self::matchesRule($value) ? $value : null;
    }

    /**
     * Value used for comparison and the unique key, or null when it breaks the rule.
     */
    public static function canonical(string $value): ?string
    {
        $display = self::display($value);

        return $display === null ? null : strtoupper($display);
    }

    private static function compatible(string $value): string
    {
        if (class_exists(Normalizer::class)) {
            $normalized = Normalizer::normalize($value, Normalizer::FORM_KC);

            return $normalized === false ? $value : $normalized;
        }

        return $value;
    }

    private static function matchesRule(string $value): bool
    {
        return preg_match('/\A[A-Za-z0-9][A-Za-z0-9-]{1,30}[A-Za-z0-9]\z/', $value) === 1;
    }
}
