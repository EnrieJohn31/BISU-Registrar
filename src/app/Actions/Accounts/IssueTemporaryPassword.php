<?php

namespace App\Actions\Accounts;

use App\Enums\AuditAction;
use App\Models\User;
use App\Support\Accounts\TemporaryPassword;
use App\Support\Accounts\UserSessions;
use App\Support\Audit\AuditRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class IssueTemporaryPassword
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Used after the administrator has verified the person in person (forgotten
     * password, or an expired temporary password).
     *
     * @return array{temporaryPassword: string, expiresAt: CarbonImmutable}
     *
     * @throws ValidationException
     */
    public function handle(User $actor, User $target, string $reason): array
    {
        if ($actor->is($target)) {
            throw ValidationException::withMessages(['reason' => __('Use your own security settings to change your password.')]);
        }

        if ($target->isDeactivated()) {
            throw ValidationException::withMessages(['reason' => __('Reactivate the account before issuing a temporary password.')]);
        }

        return DB::transaction(function () use ($actor, $target, $reason): array {
            $temporaryPassword = TemporaryPassword::generate();
            $expiresAt = TemporaryPassword::expiresAt();

            $target->forceFill([
                'password' => $temporaryPassword,
                'must_change_password' => true,
                'temporary_password_expires_at' => $expiresAt,
            ]);
            $target->setRememberToken(Str::random(60));
            $target->save();

            UserSessions::endAll($target);

            $this->audit->record(
                AuditAction::UserTemporaryPasswordIssued,
                subject: $target,
                reason: $reason,
                context: ['expires_at' => $expiresAt->toIso8601String()],
                actor: $actor,
            );

            return ['temporaryPassword' => $temporaryPassword, 'expiresAt' => $expiresAt];
        });
    }
}
