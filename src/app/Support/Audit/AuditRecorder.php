<?php

namespace App\Support\Audit;

use App\Enums\AuditAction;
use App\Enums\AuditOutcome;
use App\Models\AuditEvent;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * The single way to write audit events. Call it inside the same database
 * transaction as the change it records.
 */
final class AuditRecorder
{
    /**
     * Never written to an audit record, at any depth.
     */
    private const FORBIDDEN_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'temporary_password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'recovery_code',
        'token',
        'session_id',
    ];

    /**
     * @param  array<string, mixed>  $changes  Allow-listed fields as ['field' => ['old' => ..., 'new' => ...]]
     * @param  array<string, mixed>  $context
     */
    public function record(
        AuditAction $action,
        ?Model $subject = null,
        ?string $reason = null,
        array $changes = [],
        array $context = [],
        ?Office $office = null,
        AuditOutcome $outcome = AuditOutcome::Succeeded,
        ?User $actor = null,
        bool $system = false,
    ): AuditEvent {
        if (! $system && $actor === null) {
            $authenticated = Auth::user();
            $actor = $authenticated instanceof User ? $authenticated : null;
        }

        $actor = $system ? null : $actor;
        $changes = $this->withoutSecrets($changes);
        $context = $this->withoutSecrets($context);

        return AuditEvent::query()->create([
            'occurred_at' => now(),
            'correlation_id' => CorrelationId::current(),
            'actor_type' => $actor === null ? 'system' : 'user',
            'actor_user_id' => $actor?->id,
            'office_id' => $office?->id,
            'action' => $action->value,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject === null ? null : (string) $subject->getKey(),
            'outcome' => $outcome,
            'reason' => $reason,
            'changes' => $changes === [] ? null : $changes,
            'context' => $context === [] ? null : $context,
            'ip_address' => $this->ipAddress(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function withoutSecrets(array $values): array
    {
        $clean = [];

        foreach ($values as $key => $value) {
            if (in_array(strtolower((string) $key), self::FORBIDDEN_KEYS, true)) {
                continue;
            }

            $clean[$key] = is_array($value) ? $this->withoutSecrets($value) : $value;
        }

        return $clean;
    }

    private function ipAddress(): ?string
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return null;
        }

        return request()->ip();
    }
}
