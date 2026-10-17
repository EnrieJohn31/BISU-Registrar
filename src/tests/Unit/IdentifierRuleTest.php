<?php

namespace Tests\Unit;

use App\Support\Clients\IdentifierMask;
use App\Support\Clients\IdentifierNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The identifier rule is a labelled DEMO rule (decision D04 is still open). These
 * tests pin its behaviour so that changing it is a deliberate, reviewed act.
 */
class IdentifierRuleTest extends TestCase
{
    public function test_the_rule_version_is_stored_with_identifiers(): void
    {
        $this->assertSame(1, IdentifierNormalizer::VERSION);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function validValues(): array
    {
        // input, display form, canonical form
        return [
            'plain' => ['DEMO-S-0001', 'DEMO-S-0001', 'DEMO-S-0001'],
            'lower case is compared upper case' => ['demo-s-0001', 'demo-s-0001', 'DEMO-S-0001'],
            'leading zeroes are kept' => ['00123456', '00123456', '00123456'],
            'surrounding space is removed' => ["  DEMO-1 \t", 'DEMO-1', 'DEMO-1'],
            'shortest allowed' => ['A1B', 'A1B', 'A1B'],
            'longest allowed' => [str_repeat('A', 32), str_repeat('A', 32), str_repeat('A', 32)],
            'full-width digits are normalized' => ['１２３４', '1234', '1234'],
        ];
    }

    #[DataProvider('validValues')]
    public function test_valid_values(string $input, string $display, string $canonical): void
    {
        $this->assertSame($display, IdentifierNormalizer::display($input));
        $this->assertSame($canonical, IdentifierNormalizer::canonical($input));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidValues(): array
    {
        return [
            'empty' => [''],
            'spaces only' => ['   '],
            'too short' => ['AB'],
            'too long' => [str_repeat('A', 33)],
            'internal space' => ['DEMO 0001'],
            'underscore' => ['DEMO_0001'],
            'starts with hyphen' => ['-DEMO1'],
            'ends with hyphen' => ['DEMO1-'],
            'accented letter' => ['DÉMO-1'],
            'emoji' => ['DEMO-😀'],
            'newline inside' => ["DEMO\n001"],
            'plus sign, as in spreadsheet scientific notation' => ['1E+10'],
        ];
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_values(string $input): void
    {
        $this->assertNull(IdentifierNormalizer::display($input));
        $this->assertNull(IdentifierNormalizer::canonical($input));
    }

    public function test_masking_keeps_only_the_last_four_characters(): void
    {
        $this->assertSame('•••••••0001', IdentifierMask::mask('DEMO-S-0001'));
        $this->assertSame('••••', IdentifierMask::mask('1234'));
        $this->assertSame('•', IdentifierMask::mask('1'));
        $this->assertSame('••3456', IdentifierMask::mask('123456'));
        $this->assertSame('•••••6789', IdentifierMask::mask('123456789'));
    }
}
