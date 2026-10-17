<?php

namespace App\Actions\Accounts;

use App\Enums\AuditAction;
use App\Models\User;
use App\Support\Audit\AuditRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class CompletePasswordChange
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Replace a temporary password with one only the user knows.
     *
     * @throws ValidationException
     */
    public function handle(User $user, string $password): void
    {
        if (Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['password' => __('Choose a password different from your temporary password.')]);
        }

        DB::transaction(function () use ($user, $password): void {
            $user->forceFill([
                'password' => $password,
                'must_change_password' => false,
                'temporary_password_expires_at' => null,
            ])->save();

            $this->audit->record(AuditAction::UserPasswordChanged, subject: $user, actor: $user);
        });
    }
}
