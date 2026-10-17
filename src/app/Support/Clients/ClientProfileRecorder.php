<?php

namespace App\Support\Clients;

use App\Enums\AuditAction;
use App\Enums\ClientRevisionAction;
use App\Models\ClientProfile;
use App\Models\ClientProfileRevision;
use App\Models\User;
use App\Support\Audit\AuditRecorder;
use Illuminate\Validation\ValidationException;

/**
 * Keeps a profile's version, its revision history and the audit trail in step.
 * Use it inside the database transaction of every client action:
 * lockForChange() first, then commit() after applying the change.
 */
final class ClientProfileRecorder
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Lock the profile row and reject an edit made from a stale screen.
     *
     * @throws ValidationException
     */
    public function lockForChange(ClientProfile $profile, int $expectedVersion): ClientProfile
    {
        $locked = ClientProfile::query()->lockForUpdate()->findOrFail($profile->id);

        if ($locked->version !== $expectedVersion) {
            throw ValidationException::withMessages([
                'version' => __('This client profile was changed by someone else. The latest details are shown; review them and try again.'),
            ]);
        }

        return $locked;
    }

    /**
     * Save the profile with its next version and write the revision and audit
     * event. Revision numbers equal profile versions, so the two never drift.
     *
     * The revision holds the full old and new values and is shown only to staff
     * who may view clients. The audit trail gets only what the caller passes as
     * `$auditChanges` and `$auditContext`: no names and no full identifiers.
     *
     * @param  array<string, array{old: mixed, new: mixed}>  $changes
     * @param  array<string, array{old: mixed, new: mixed}>  $auditChanges
     * @param  array<string, mixed>  $auditContext
     */
    public function commit(
        ClientProfile $profile,
        ClientRevisionAction $action,
        AuditAction $auditAction,
        User $actor,
        string $reason,
        array $changes,
        array $auditChanges = [],
        array $auditContext = [],
    ): ClientProfileRevision {
        $next = (int) ClientProfileRevision::query()->where('client_profile_id', $profile->id)->max('revision_number') + 1;

        $profile->version = $next;
        $profile->save();

        $revision = ClientProfileRevision::query()->create([
            'client_profile_id' => $profile->id,
            'revision_number' => $next,
            'action' => $action,
            'changes' => $changes === [] ? null : $changes,
            'reason' => $reason,
            'actor_user_id' => $actor->id,
            'occurred_at' => now(),
        ]);

        $profile->loadMissing('office');

        $this->audit->record(
            $auditAction,
            subject: $profile,
            reason: $reason,
            changes: $auditChanges,
            context: ['revision' => $next, ...$auditContext],
            office: $profile->office,
            actor: $actor,
        );

        return $revision;
    }
}
