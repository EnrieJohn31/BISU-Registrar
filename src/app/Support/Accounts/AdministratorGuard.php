<?php

namespace App\Support\Accounts;

use App\Enums\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Validation\ValidationException;

final class AdministratorGuard
{
    /**
     * Call inside the transaction that removes an administrator (revoking the
     * role or deactivating the account). Locking the active administrator
     * assignments serializes these operations, so two administrators removing
     * each other at the same moment cannot leave the campus with none.
     *
     * @throws ValidationException
     */
    public static function ensureAnotherAdministratorRemains(User $actor, User $target, string $field): void
    {
        $activeAdministratorIds = UserRole::query()
            ->where('role', Role::SystemAdmin->value)
            ->whereNull('revoked_at')
            ->whereHas('user', fn ($query) => $query->whereNull('deactivated_at'))
            ->lockForUpdate()
            ->pluck('user_id')
            ->unique();

        if (! $activeAdministratorIds->contains($actor->id)) {
            throw ValidationException::withMessages([
                $field => __('Your administrator access has changed. Reload the page and try again.'),
            ]);
        }

        if ($activeAdministratorIds->reject(fn (int $id): bool => $id === $target->id)->isEmpty()) {
            throw ValidationException::withMessages([
                $field => __('At least one active system administrator must remain.'),
            ]);
        }
    }
}
