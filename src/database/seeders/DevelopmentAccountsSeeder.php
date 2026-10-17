<?php

namespace Database\Seeders;

use App\Actions\Accounts\AssignRole;
use App\Enums\OfficeCode;
use App\Enums\Role;
use App\Models\Office;
use App\Models\User;
use App\Support\Accounts\TemporaryPassword;
use Illuminate\Database\Seeder;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\RecoveryCode;

/**
 * Local test accounts, one per role, written to Credentials.md (git-ignored).
 * Never runs in production. Re-running resets these accounts and issues new
 * two-factor secrets.
 */
class DevelopmentAccountsSeeder extends Seeder
{
    public const PASSWORD = 'password';

    private const REASON = 'Local development test account (DevelopmentAccountsSeeder)';

    public function __construct(
        private readonly AssignRole $assignRole,
        private readonly TwoFactorAuthenticationProvider $twoFactor,
    ) {}

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->report('Skipped: development test accounts are never created in production.');

            return;
        }

        $accounts = [
            ['System administrator', 'admin@bisu.test', [[Role::SystemAdmin, null]], 'Manages accounts, roles and the audit trail'],
            ['Second administrator', 'admin2@bisu.test', [[Role::SystemAdmin, null]], 'For testing administrator-on-administrator actions'],
            ['Intake Staff', 'intake@bisu.test', [[Role::IntakeStaff, OfficeCode::Registrar]], ''],
            ['Registrar Processor', 'processor@bisu.test', [[Role::RegistrarProcessor, OfficeCode::Registrar]], ''],
            ['Registrar Approver', 'approver@bisu.test', [[Role::RegistrarApprover, OfficeCode::Registrar]], ''],
            ['Release Officer', 'release@bisu.test', [[Role::ReleaseOfficer, OfficeCode::Registrar]], ''],
            ['Cashier', 'cashier@bisu.test', [[Role::Cashier, OfficeCode::Cashier]], ''],
            ['Registrar Supervisor', 'registrar.supervisor@bisu.test', [[Role::OfficeSupervisor, OfficeCode::Registrar]], 'Sees Registrar audit events only'],
            ['Cashier Supervisor', 'cashier.supervisor@bisu.test', [[Role::OfficeSupervisor, OfficeCode::Cashier]], 'Sees Cashier audit events only'],
            ['Report Exporter', 'exporter@bisu.test', [[Role::ReportExporter, OfficeCode::Registrar]], ''],
            ['Supplier Staff', 'supplier.staff@bisu.test', [[Role::SupplierStaff, OfficeCode::Registrar]], ''],
            ['Supplier Supervisor', 'supplier.supervisor@bisu.test', [[Role::SupplierSupervisor, OfficeCode::Registrar]], ''],
            ['Client Account', 'client@bisu.test', [], 'Self-registered style account with no roles'],
            ['New Staff Member', 'new.staff@bisu.test', [[Role::IntakeStaff, OfficeCode::Registrar]], 'First sign-in flow: must replace the temporary password'],
            ['Deactivated Staff', 'deactivated@bisu.test', [[Role::RegistrarProcessor, OfficeCode::Registrar]], 'Sign-in is refused'],
        ];

        $rows = [];

        foreach ($accounts as [$name, $email, $roles, $note]) {
            $user = $this->resetAccount($name, $email);

            foreach ($roles as [$role, $officeCode]) {
                $office = $officeCode === null ? null : Office::forCode($officeCode);

                if (! $user->hasRole($role, $office)) {
                    $this->assignRole->handle(null, $user, $role, $office, self::REASON);
                }
            }

            $row = ['name' => $name, 'email' => $email, 'password' => self::PASSWORD, 'roles' => $this->describeRoles($user), 'note' => $note];

            if ($email === 'new.staff@bisu.test') {
                $temporaryPassword = TemporaryPassword::generate();
                $user->forceFill([
                    'password' => $temporaryPassword,
                    'must_change_password' => true,
                    'temporary_password_expires_at' => TemporaryPassword::expiresAt(),
                ])->save();
                $row['password'] = $temporaryPassword;
            }

            if ($user->requiresTwoFactor()) {
                $row += $this->enableTwoFactor($user);
            }

            if ($email === 'deactivated@bisu.test') {
                $user->forceFill(['deactivated_at' => now(), 'deactivation_reason' => self::REASON])->save();
            }

            $rows[] = $row;
        }

        $this->writeCredentialsFile($rows);

        $this->report('Development accounts ready. Sign-in details: '.base_path('Credentials.md'));
    }

    private function report(string $message): void
    {
        $this->command->info($message);
    }

    private function resetAccount(string $name, string $email): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'password' => self::PASSWORD,
            'email_verified_at' => now(),
            'deactivated_at' => null,
            'deactivated_by_id' => null,
            'deactivation_reason' => null,
            'must_change_password' => false,
            'temporary_password_expires_at' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $user->forgetRoles();

        return $user;
    }

    /**
     * @return array{secret: string, recovery_codes: list<string>}
     */
    private function enableTwoFactor(User $user): array
    {
        $secret = $this->twoFactor->generateSecretKey();
        $recoveryCodes = [];

        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = RecoveryCode::generate();
        }

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt((string) json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return ['secret' => $secret, 'recovery_codes' => $recoveryCodes];
    }

    private function describeRoles(User $user): string
    {
        $user->load('activeRoleAssignments.office');

        $roles = $user->activeRoleAssignments
            ->map(fn ($assignment): string => $assignment->role->label().($assignment->office ? ' ('.$assignment->office->name.')' : ''))
            ->implode(', ');

        return $roles === '' ? 'None (client)' : $roles;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function writeCredentialsFile(array $rows): void
    {
        $lines = [
            '# BISU Registrar test accounts',
            '',
            '> **Local development only.** Generated by `DevelopmentAccountsSeeder` on '.now()->timezone('Asia/Manila')->toDayDateTimeString().' (Asia/Manila).',
            '> This file is git-ignored. Never commit it, and never create these accounts on a campus server.',
            '',
            'Regenerate (resets these accounts and issues new two-factor secrets):',
            '',
            '```powershell',
            'docker compose -f docker-compose.dev.yml exec app php artisan db:seed --class=DevelopmentAccountsSeeder',
            '```',
            '',
            'App: http://localhost:8085/login',
            '',
            '## Accounts',
            '',
            '| Name | Email | Password | Roles | 2FA | Notes |',
            '|---|---|---|---|---|---|',
        ];

        foreach ($rows as $row) {
            $lines[] = sprintf(
                '| %s | `%s` | `%s` | %s | %s | %s |',
                $row['name'],
                $row['email'],
                $row['password'],
                $row['roles'],
                isset($row['secret']) ? 'Yes' : 'No',
                $row['note'],
            );
        }

        $lines = [...$lines, '', '## Two-factor codes', '', 'Accounts marked 2FA need a 6-digit code after the password. Either:', '', '- add the secret to an authenticator app (manual entry, time-based), or', '- print the current code from the container:', '', '```powershell', 'docker compose -f docker-compose.dev.yml exec app php artisan tinker --execute="echo (new PragmaRX\\Google2FA\\Google2FA)->getCurrentOtp(\'SECRET\');"', '```', '', '- or use a recovery code on the challenge page ("use a recovery code"). Each works once.', ''];

        foreach ($rows as $row) {
            if (! isset($row['secret'])) {
                continue;
            }

            /** @var list<string> $codes */
            $codes = $row['recovery_codes'];

            $lines[] = "### {$row['email']}";
            $lines[] = '';
            $lines[] = "- Secret: `{$row['secret']}`";
            $lines[] = '- Recovery codes: '.implode(', ', array_map(fn (string $code): string => "`{$code}`", $codes));
            $lines[] = '';
        }

        file_put_contents(base_path('Credentials.md'), implode(PHP_EOL, $lines));
    }
}
