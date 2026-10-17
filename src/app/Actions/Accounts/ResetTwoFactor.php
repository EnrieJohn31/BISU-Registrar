<?php

namespace App\Actions\Accounts;

use App\Enums\AuditAction;
use App\Models\User;
use App\Support\Audit\AuditRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ResetTwoFactor
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Used when a staff member lost their authenticator device, after the
     * administrator verified them in person. Roles that require two-factor send
     * the person to enroll again at their next request.
     *
     * @throws ValidationException
     */
    public function handle(User $actor, User $target, string $reason): void
    {
        if ($actor->is($target)) {
            throw ValidationException::withMessages(['reason' => __('Use your own security settings to manage two-factor authentication.')]);
        }

        if ($target->two_factor_secret === null) {
            throw ValidationException::withMessages(['reason' => __('Two-factor authentication is not set up for this account.')]);
        }

        DB::transaction(function () use ($actor, $target, $reason): void {
            $target->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->save();

            $this->audit->record(AuditAction::UserTwoFactorReset, subject: $target, reason: $reason, actor: $actor);
        });
    }
}
