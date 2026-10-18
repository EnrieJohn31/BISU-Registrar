<?php

namespace Tests\Feature;

use Database\Seeders\BrowserTestSeeder;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BrowserFixtureSafetyTest extends TestCase
{
    /** @return array<string, array{string, string, string, string}> */
    public static function rejectedEnvironments(): array
    {
        return [
            'local environment' => ['local', '1', 'sqlite', 'expected'],
            'missing browser flag' => ['testing', '0', 'sqlite', 'expected'],
            'another database driver' => ['testing', '1', 'not-a-real-driver', 'expected'],
            'another database path' => ['testing', '1', 'sqlite', ':memory:'],
        ];
    }

    #[DataProvider('rejectedEnvironments')]
    public function test_browser_fixtures_refuse_other_environments_before_accessing_data(string $environment, string $flag, string $driver, string $path): void
    {
        app()->instance('env', $environment);
        config([
            'browser.fixtures_enabled' => $flag === '1',
            'database.default' => $driver,
            'database.connections.sqlite.database' => $path === 'expected' ? storage_path('browser/database.sqlite') : $path,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Browser fixtures require the isolated testing environment and database path.');
        app(BrowserTestSeeder::class)->run();
    }
}
