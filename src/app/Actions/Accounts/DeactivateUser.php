<?php

namespace App\Actions\Accounts;

use App\Enums\AuditAction;
use App\Enums\Role;
use App\Models\User;
use App\Support\Accounts\AdministratorGuard;
use App\Support\Accounts\UserSessions;
use App\Support\Audit\AuditRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class DeactivateUser
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Deactivation keeps the account, its role history and its audit trail. It
     * ends every session and invalidates remember-me cookies.
     *
     * @throws ValidationException
     */
    public function handle(User $actor, User $target, string $reason): void
    {
        if ($actor->is($target)) {
            throw ValidationException::withMessages(['reason' => __('You cannot deactivate your own account.')]);
        }

        DB::transaction(function () use ($actor, $target, $reason): void {
            $locked = User::query()->lockForUpdate()->findOrFail($target->id);

            if ($locked->isDeactivated()) {
                throw ValidationException::withMessages(['reason' => __('This account is already deactivated.')]);
            }

            if ($locked->hasRole(Role::SystemAdmin)) {
                AdministratorGuard::ensureAnotherAdministratorRemains($actor, $locked, 'reason');
            }

            $locked->forceFill([
                'deactivated_at' => now(),
                'deactivated_by_id' => $actor->id,
                'deactivation_reason' => $reason,
            ]);
            $locked->setRememberToken(Str::random(60));
            $locked->save();

            UserSessions::endAll($locked);

            $this->audit->record(AuditAction::UserDeactivated, subject: $locked, reason: $reason, actor: $actor);
        });

        $target->refresh();
    }
}
