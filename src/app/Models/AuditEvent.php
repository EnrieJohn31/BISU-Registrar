<?php

namespace App\Models;

use App\Enums\AuditOutcome;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * An append-only audit record. Write it with App\Support\Audit\AuditRecorder.
 *
 * @property int $id
 * @property Carbon $occurred_at
 * @property string $correlation_id
 * @property string $actor_type
 * @property int|null $actor_user_id
 * @property int|null $office_id
 * @property string $action
 * @property string|null $subject_type
 * @property string|null $subject_id
 * @property AuditOutcome $outcome
 * @property string|null $reason
 * @property array<string, mixed>|null $changes
 * @property array<string, mixed>|null $context
 * @property string|null $ip_address
 */
#[Table(name: 'audit_events', timestamps: false, dateFormat: 'Y-m-d H:i:s.u')]
#[Fillable([
    'occurred_at',
    'correlation_id',
    'actor_type',
    'actor_user_id',
    'office_id',
    'action',
    'subject_type',
    'subject_id',
    'outcome',
    'reason',
    'changes',
    'context',
    'ip_address',
])]
class AuditEvent extends Model
{
    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit events are append-only and cannot be changed.'));
        static::deleting(fn () => throw new LogicException('Audit events are append-only and cannot be deleted.'));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /**
     * @return BelongsTo<Office, $this>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'outcome' => AuditOutcome::class,
            'changes' => 'array',
            'context' => 'array',
        ];
    }
}
