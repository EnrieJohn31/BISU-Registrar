<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A role assignment. Assignments are revoked, never deleted, so the history of
 * who held which role, when and why is kept.
 *
 * @property int $id
 * @property int $user_id
 * @property Role $role
 * @property int|null $office_id
 * @property int|null $assigned_by_id
 * @property Carbon $assigned_at
 * @property string $assignment_reason
 * @property Carbon|null $revoked_at
 * @property int|null $revoked_by_id
 * @property string|null $revoke_reason
 */
#[Table(name: 'user_roles', timestamps: false)]
#[Fillable([
    'user_id',
    'role',
    'office_id',
    'assigned_by_id',
    'assigned_at',
    'assignment_reason',
])]
class UserRole extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Office, $this>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_id');
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    /**
     * @param  Builder<UserRole>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('revoked_at');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'assigned_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
