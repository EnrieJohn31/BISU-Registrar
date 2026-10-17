<?php

namespace App\Actions\Accounts;

use App\Enums\AuditAction;
use App\Models\User;
use App\Support\Audit\AuditRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class UpdateUserDetails
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Administrator correction of a name or email. A staff email stays verified
     * (the administrator vouches for it); a client must verify the new email.
     *
     * @throws ValidationException
     */
    public function handle(User $actor, User $target, string $name, string $email, string $reason): void
    {
        if ($actor->is($target)) {
            throw ValidationException::withMessages(['reason' => __('Use your own profile settings to change your details.')]);
        }

        $email = Str::lower($email);
        $changes = [];

        if ($target->name !== $name) {
            $changes['name'] = ['old' => $target->name, 'new' => $name];
        }

        if ($target->email !== $email) {
            $changes['email'] = ['old' => $target->email, 'new' => $email];
        }

        if ($changes === []) {
            return;
        }

        DB::transaction(function () use ($actor, $target, $name, $email, $reason, $changes): void {
            $target->forceFill(['name' => $name, 'email' => $email]);

            if (isset($changes['email']) && ! $target->isStaff()) {
                $target->email_verified_at = null;
            }

            $target->save();

            $this->audit->record(AuditAction::UserUpdated, subject: $target, reason: $reason, changes: $changes, actor: $actor);
        });
    }
}
