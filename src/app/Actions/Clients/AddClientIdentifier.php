<?php

namespace App\Actions\Clients;

use App\Enums\AuditAction;
use App\Enums\ClientRevisionAction;
use App\Enums\IdentifierType;
use App\Models\ClientIdentifier;
use App\Models\ClientProfile;
use App\Models\User;
use App\Support\Clients\ClientProfileRecorder;
use App\Support\Clients\IdentifierMask;
use App\Support\Clients\IdentifierNormalizer;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AddClientIdentifier
{
    public function __construct(private readonly ClientProfileRecorder $recorder) {}

    /**
     * Add an ID to an existing profile, for example an Alumni ID for a person who
     * already has a Student ID. It never creates a second person.
     *
     * @throws ValidationException
     */
    public function handle(
        User $actor,
        ClientProfile $profile,
        int $expectedVersion,
        IdentifierType $type,
        string $identifierValue,
        string $reason,
    ): ClientIdentifier {
        $display = IdentifierNormalizer::display($identifierValue);
        $canonical = IdentifierNormalizer::canonical($identifierValue);

        if ($display === null || $canonical === null) {
            throw ValidationException::withMessages(['identifier_value' => IdentifierNormalizer::DESCRIPTION]);
        }

        return DB::transaction(function () use ($actor, $profile, $expectedVersion, $type, $display, $canonical, $reason): ClientIdentifier {
            $locked = $this->recorder->lockForChange($profile, $expectedVersion);

            // Retired IDs stay in the key, so a retired ID can never be recorded again.
            if (ClientIdentifier::query()->where('type', $type->value)->where('canonical_value', $canonical)->exists()) {
                throw $this->identifierTaken();
            }

            try {
                $identifier = new ClientIdentifier;
                $identifier->forceFill([
                    'client_profile_id' => $locked->id,
                    'type' => $type,
                    'display_value' => $display,
                    'canonical_value' => $canonical,
                    'normalization_version' => IdentifierNormalizer::VERSION,
                    'added_by_id' => $actor->id,
                    'added_at' => now(),
                ])->save();
            } catch (UniqueConstraintViolationException) {
                throw $this->identifierTaken();
            }

            $this->recorder->commit(
                $locked,
                ClientRevisionAction::IdentifierAdded,
                AuditAction::ClientIdentifierAdded,
                $actor,
                $reason,
                changes: ['identifier' => ['old' => null, 'new' => ['type' => $type->value, 'value' => $display]]],
                auditContext: [
                    'identifier_id' => $identifier->id,
                    'identifier_type' => $type->value,
                    'identifier_masked' => IdentifierMask::mask($display),
                ],
            );

            return $identifier;
        });
    }

    private function identifierTaken(): ValidationException
    {
        return ValidationException::withMessages([
            'identifier_value' => __('This ID is already recorded on a client profile, current or retired.'),
        ]);
    }
}
