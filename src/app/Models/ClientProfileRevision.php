<?php

namespace App\Models;

use App\Enums\ClientRevisionAction;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * Append-only history of a profile and its identifiers.
 *
 * @property int $id
 * @property int $client_profile_id
 * @property int $revision_number
 * @property ClientRevisionAction $action
 * @property array<string, mixed>|null $changes
 * @property string $reason
 * @property int $actor_user_id
 * @property Carbon $occurred_at
 */
#[Table(name: 'client_profile_revisions', timestamps: false, dateFormat: 'Y-m-d H:i:s.u')]
#[Fillable([
    'client_profile_id',
    'revision_number',
    'action',
    'changes',
    'reason',
    'actor_user_id',
    'occurred_at',
])]
class ClientProfileRevision extends Model
{
    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Client profile revisions are append-only and cannot be changed.'));
        static::deleting(fn () => throw new LogicException('Client profile revisions are append-only and cannot be deleted.'));
    }

    /**
     * @return BelongsTo<ClientProfile, $this>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class, 'client_profile_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => ClientRevisionAction::class,
            'changes' => 'array',
            'occurred_at' => 'datetime',
            'revision_number' => 'integer',
        ];
    }
}
