<?php

namespace Database\Seeders;

use App\Actions\Clients\CorrectClientProfile;
use App\Actions\Clients\CreateClientProfile;
use App\Enums\ClientCategory;
use App\Enums\IdentifierType;
use App\Enums\Role;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use LogicException;

/** Synthetic fixtures for the disposable browser project, never development or campus data. */
class BrowserTestSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('testing') || config('browser.fixtures_enabled') !== true
            || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== storage_path('browser/database.sqlite')) {
            throw new LogicException('Browser fixtures require the isolated testing environment and database path.');
        }

        if (User::query()->exists() || ClientProfile::query()->exists()) {
            throw new LogicException('Browser fixtures require an empty disposable database; no records were reset.');
        }

        $password = Str::password(24);
        $accounts = [];
        foreach (['processor' => Role::RegistrarProcessor, 'intake' => Role::IntakeStaff, 'admin' => Role::SystemAdmin, 'cashier' => Role::Cashier] as $key => $role) {
            $secret = $role->requiresTwoFactor() ? app(TwoFactorAuthenticationProvider::class)->generateSecretKey() : null;
            $user = User::factory()->withRole($role)->create([
                'name' => ucfirst($key).' Browser (Demo)',
                'email' => $key.'@bisu.test',
                'password' => $password,
                'two_factor_secret' => $secret === null ? null : encrypt($secret),
                'two_factor_recovery_codes' => $secret === null ? null : encrypt(json_encode([Str::random(20)], JSON_THROW_ON_ERROR)),
                'two_factor_confirmed_at' => $secret === null ? null : now(),
            ]);
            $accounts[$key] = ['email' => $user->email, 'password' => $password, 'totpSecret' => $secret];
        }

        $this->call(DevelopmentClientsSeeder::class);
        $processor = User::query()->where('email', 'processor@bisu.test')->sole();
        $history = app(CreateClientProfile::class)->handle($processor, 'History original (Demo)', ClientCategory::Student, 'Browser source (Demo)', IdentifierType::Student, 'DEMO-HISTORY-0001', 'Browser history creation (Demo)');
        for ($version = 1; $version <= 50; $version++) {
            app(CorrectClientProfile::class)->handle($processor, $history, $version, $version === 50 ? 'History pagination (Demo)' : "History revision {$version} (Demo)", ClientCategory::Student, "Browser history correction {$version} (Demo)");
        }

        File::ensureDirectoryExists(storage_path('browser/auth'));
        File::put(storage_path('browser/fixtures.json'), json_encode([
            'accounts' => $accounts,
            'historyProfileId' => $history->id,
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        $this->command->info('Isolated browser fixtures ready; generated credentials remain in disposable storage.');
    }
}
