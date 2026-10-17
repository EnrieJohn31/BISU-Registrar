<?php

namespace App\Models;

use App\Enums\Permission;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $deactivated_at
 * @property int|null $deactivated_by_id
 * @property string|null $deactivation_reason
 * @property bool $must_change_password
 * @property Carbon|null $temporary_password_expires_at
 * @property Carbon|null $last_login_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, UserRole> $roleAssignments
 * @property-read Collection<int, UserRole> $activeRoleAssignments
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Every role assignment, including revoked ones (history).
     *
     * @return HasMany<UserRole, $this>
     */
    public function roleAssignments(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * @return HasMany<UserRole, $this>
     */
    public function activeRoleAssignments(): HasMany
    {
        return $this->hasMany(UserRole::class)->whereNull('revoked_at');
    }

    public function isDeactivated(): bool
    {
        return $this->deactivated_at !== null;
    }

    public function hasTemporaryPasswordExpired(): bool
    {
        return $this->must_change_password
            && $this->temporary_password_expires_at !== null
            && $this->temporary_password_expires_at->isPast();
    }

    /**
     * True when the user holds at least one staff role.
     */
    public function isStaff(): bool
    {
        return $this->currentAssignments()->isNotEmpty();
    }

    /**
     * Check a permission. With an office, only assignments in that office (or
     * campus-wide ones) count. Deactivated users hold no permissions.
     */
    public function hasPermission(Permission $permission, ?Office $office = null): bool
    {
        return $this->currentAssignments()->contains(
            fn (UserRole $assignment): bool => in_array($permission, $assignment->role->permissions(), true)
                && ($office === null || $assignment->office_id === null || $assignment->office_id === $office->id),
        );
    }

    public function hasRole(Role $role, ?Office $office = null): bool
    {
        return $this->currentAssignments()->contains(
            fn (UserRole $assignment): bool => $assignment->role === $role
                && ($office === null || $assignment->office_id === $office->id),
        );
    }

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        $permissions = [];

        foreach ($this->currentAssignments() as $assignment) {
            foreach ($assignment->role->permissions() as $permission) {
                $permissions[$permission->value] = $permission;
            }
        }

        return array_values($permissions);
    }

    /**
     * Offices in which the user holds the permission; null when it is held
     * campus-wide (all offices).
     *
     * @return list<int>|null
     */
    public function officeIdsWithPermission(Permission $permission): ?array
    {
        $officeIds = [];

        foreach ($this->currentAssignments() as $assignment) {
            if (! in_array($permission, $assignment->role->permissions(), true)) {
                continue;
            }

            if ($assignment->office_id === null) {
                return null;
            }

            $officeIds[] = $assignment->office_id;
        }

        return array_values(array_unique($officeIds));
    }

    public function requiresTwoFactor(): bool
    {
        return $this->currentAssignments()->contains(
            fn (UserRole $assignment): bool => $assignment->role->requiresTwoFactor(),
        );
    }

    /**
     * Drop the per-request role cache after assignments change.
     */
    public function forgetRoles(): void
    {
        $this->unsetRelation('activeRoleAssignments');
    }

    /**
     * Active assignments, loaded once per request.
     *
     * @return Collection<int, UserRole>
     */
    private function currentAssignments(): Collection
    {
        if ($this->isDeactivated()) {
            return new Collection;
        }

        return $this->activeRoleAssignments;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'must_change_password' => 'boolean',
            'temporary_password_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }
}
