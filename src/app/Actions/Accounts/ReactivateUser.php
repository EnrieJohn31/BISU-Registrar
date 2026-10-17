<?php

namespace App\Actions\Accounts;

use App\Enums\AuditAction;
use App\Models\User;
use App\Support\Audit\AuditRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReactivateUser
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * @throws ValidationException
     */
    public function handle(User $actor, User $target, string $reason): void
    {
        DB::transaction(function () use ($actor, $target, $reason): void {
            $locked = User::query()->lockForUpdate()->findOrFail($target->id);

            if (! $locked->isDeactivated()) {
                throw ValidationException::withMessages(['reason' => __('This account is already active.')]);
            }

            $locked->forceFill([
                'deactivated_at' => null,
                'deactivated_by_id' => null,
                'deactivation_reason' => null,
            ])->save();

            $this->audit->record(AuditAction::UserReactivated, subject: $locked, reason: $reason, actor: $actor);
        });

        $target->refresh();
    }
}
