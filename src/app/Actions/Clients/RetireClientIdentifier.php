<?php

namespace App\Actions\Clients;

use App\Enums\AuditAction;
use App\Enums\ClientRevisionAction;
use App\Models\ClientIdentifier;
use App\Models\ClientProfile;
use App\Models\User;
use App\Support\Clients\ClientProfileRecorder;
use App\Support\Clients\IdentifierMask;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RetireClientIdentifier
{
    public function __construct(private readonly ClientProfileRecorder $recorder) {}

    /**
     * Retire an ID (a typing mistake, or an ID that is no longer used). It is kept
     * as history and can never be reassigned to another person.
     *
     * @throws ValidationException
     */
    public function handle(User $actor, ClientProfile $profile, ClientIdentifier $identifier, int $expectedVersion, string $reason): void
    {
        DB::transaction(function () use ($actor, $profile, $identifier, $expectedVersion, $reason): void {
            $locked = $this->recorder->lockForChange($profile, $expectedVersion);

            $target = ClientIdentifier::query()
                ->where('client_profile_id', $locked->id)
                ->lockForUpdate()
                ->find($identifier->id);

            if ($target === null) {
                throw ValidationException::withMessages(['reason' => __('This ID does not belong to this profile.')]);
            }

            if (! $target->isCurrent()) {
                throw ValidationException::withMessages(['reason' => __('This ID was already retired.')]);
            }

            if ($locked->currentIdentifiers()->count() < 2) {
                throw ValidationException::withMessages([
                    'reason' => __('A profile must keep at least one current ID. Add the correct ID first, then retire this one.'),
                ]);
            }

            $target->forceFill([
                'retired_at' => now(),
                'retired_by_id' => $actor->id,
                'retire_reason' => $reason,
            ])->save();

            $this->recorder->commit(
                $locked,
                ClientRevisionAction::IdentifierRetired,
                AuditAction::ClientIdentifierRetired,
                $actor,
                $reason,
                changes: ['identifier' => ['old' => ['type' => $target->type->value, 'value' => $target->display_value], 'new' => null]],
                auditContext: [
                    'identifier_id' => $target->id,
                    'identifier_type' => $target->type->value,
                    'identifier_masked' => IdentifierMask::mask($target->display_value),
                ],
            );
        });
    }
}
