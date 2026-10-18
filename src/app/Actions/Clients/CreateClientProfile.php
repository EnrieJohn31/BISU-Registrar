<?php

namespace App\Actions\Clients;

use App\Enums\AuditAction;
use App\Enums\ClientCategory;
use App\Enums\ClientRevisionAction;
use App\Enums\IdentifierType;
use App\Enums\OfficeCode;
use App\Models\ClientIdentifier;
use App\Models\ClientProfile;
use App\Models\Office;
use App\Models\User;
use App\Support\Clients\ClientProfileRecorder;
use App\Support\Clients\IdentifierMask;
use App\Support\Clients\IdentifierNormalizer;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateClientProfile
{
    public function __construct(private readonly ClientProfileRecorder $recorder) {}

    /**
     * Create a profile with its first identifier. People are never merged by name:
     * two profiles may share a name, but never an identifier.
     *
     * @throws ValidationException
     */
    public function handle(
        User $actor,
        string $displayName,
        ClientCategory $category,
        string $sourceReference,
        IdentifierType $type,
        string $identifierValue,
        string $reason,
    ): ClientProfile {
        $display = IdentifierNormalizer::display($identifierValue);
        $canonical = IdentifierNormalizer::canonical($identifierValue);

        if ($display === null || $canonical === null) {
            throw ValidationException::withMessages(['identifier_value' => IdentifierNormalizer::DESCRIPTION]);
        }

        return DB::transaction(function () use ($actor, $displayName, $category, $sourceReference, $type, $display, $canonical, $reason): ClientProfile {
            $this->ensureIdentifierIsFree($type, $canonical);

            $profile = new ClientProfile([
                'office_id' => Office::forCode(OfficeCode::Registrar)->id,
                'display_name' => $displayName,
                'category' => $category,
                'source_reference' => $sourceReference,
            ]);
            $profile->forceFill(['created_by_id' => $actor->id])->save();

            try {
                $identifier = new ClientIdentifier;
                $identifier->forceFill([
                    'client_profile_id' => $profile->id,
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
                $profile,
                ClientRevisionAction::Created,
                AuditAction::ClientCreated,
                $actor,
                $reason,
                changes: [
                    'display_name' => ['old' => null, 'new' => $displayName],
                    'category' => ['old' => null, 'new' => $category->value],
                    'source_reference' => ['old' => null, 'new' => $sourceReference],
                    'identifier' => ['old' => null, 'new' => ['type' => $type->value, 'value' => $display]],
                ],
                auditChanges: ['category' => ['old' => null, 'new' => $category->value]],
                auditContext: [
                    'identifier_id' => $identifier->id,
                    'identifier_type' => $type->value,
                    'identifier_masked' => IdentifierMask::mask($display),
                ],
            );

            return $profile;
        });
    }

    private function ensureIdentifierIsFree(IdentifierType $type, string $canonical): void
    {
        if (ClientIdentifier::query()->where('type', $type->value)->where('canonical_value', $canonical)->exists()) {
            throw $this->identifierTaken();
        }
    }

    private function identifierTaken(): ValidationException
    {
        return ValidationException::withMessages([
            'identifier_value' => __('This ID is already recorded on a client profile, current or retired. Search for it instead of creating a duplicate.'),
        ]);
    }
}
