<?php

namespace App\Actions\Accounts;

use App\Enums\AuditAction;
use App\Enums\Role;
use App\Models\Office;
use App\Models\User;
use App\Support\Accounts\TemporaryPassword;
use App\Support\Audit\AuditRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ProvisionStaffUser
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly AssignRole $assignRole,
    ) {}

    /**
     * Create a staff account handed over in person: there is no mail relay yet
     * (D11), so the email is marked verified by the administrator and the
     * person must replace the temporary password at first sign-in.
     *
     * @param  list<Role>  $roles
     * @return array{user: User, temporaryPassword: string, expiresAt: CarbonImmutable}
     */
    public function handle(?User $actor, string $name, string $email, ?Office $office, array $roles, string $reason): array
    {
        return DB::transaction(function () use ($actor, $name, $email, $office, $roles, $reason): array {
            $temporaryPassword = TemporaryPassword::generate();
            $expiresAt = TemporaryPassword::expiresAt();

            $user = new User;
            $user->forceFill([
                'name' => $name,
                'email' => Str::lower($email),
                'password' => $temporaryPassword,
                'email_verified_at' => now(),
                'must_change_password' => true,
                'temporary_password_expires_at' => $expiresAt,
            ])->save();

            $this->audit->record(
                AuditAction::UserProvisioned,
                subject: $user,
                reason: $reason,
                changes: [
                    'name' => ['old' => null, 'new' => $user->name],
                    'email' => ['old' => null, 'new' => $user->email],
                ],
                context: ['email_verification' => 'in person, by the provisioning administrator'],
                actor: $actor,
                system: $actor === null,
            );

            foreach ($roles as $role) {
                $this->assignRole->handle($actor, $user, $role, $role->isCampusWide() ? null : $office, $reason);
            }

            return ['user' => $user, 'temporaryPassword' => $temporaryPassword, 'expiresAt' => $expiresAt];
        });
    }
}
