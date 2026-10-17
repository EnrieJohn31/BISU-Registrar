<?php

namespace App\Actions\Accounts;

use App\Enums\AuditAction;
use App\Enums\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Support\Accounts\AdministratorGuard;
use App\Support\Audit\AuditRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RevokeRole
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * @throws ValidationException
     */
    public function handle(User $actor, UserRole $assignment, string $reason): void
    {
        $target = $assignment->user;

        if ($actor->is($target)) {
            throw ValidationException::withMessages(['reason' => __('You cannot change your own roles.')]);
        }

        DB::transaction(function () use ($actor, $assignment, $target, $reason): void {
            $locked = UserRole::query()->lockForUpdate()->findOrFail($assignment->id);

            if (! $locked->isActive()) {
                throw ValidationException::withMessages(['reason' => __('This role was already revoked.')]);
            }

            if ($locked->role === Role::SystemAdmin) {
                AdministratorGuard::ensureAnotherAdministratorRemains($actor, $target, 'reason');
            }

            $locked->forceFill([
                'revoked_at' => now(),
                'revoked_by_id' => $actor->id,
                'revoke_reason' => $reason,
            ])->save();

            $this->audit->record(
                AuditAction::RoleRevoked,
                subject: $target,
                reason: $reason,
                context: [
                    'role' => $locked->role->value,
                    'office' => $locked->office?->code->value,
                    'assignment_id' => $locked->id,
                ],
                office: $locked->office,
                actor: $actor,
            );

            $target->forgetRoles();
        });
    }
}
