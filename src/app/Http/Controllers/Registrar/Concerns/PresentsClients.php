<?php

namespace App\Http\Controllers\Registrar\Concerns;

use App\Enums\ClientCategory;
use App\Enums\IdentifierType;
use App\Enums\VerificationState;
use App\Models\ClientIdentifier;
use App\Models\ClientProfile;
use App\Models\ClientProfileRevision;
use App\Support\Clients\IdentifierMask;
use App\Support\Clients\IdentifierNormalizer;

trait PresentsClients
{
    /**
     * A list row: IDs are masked; the full value is on the detail page.
     *
     * @return array<string, mixed>
     */
    protected function clientSummary(ClientProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'display_name' => $profile->display_name,
            'category' => $profile->category->value,
            'category_label' => $profile->category->label(),
            'verification_state' => $profile->verification_state->value,
            'verification_label' => $profile->verification_state->label(),
            'identifiers' => $profile->currentIdentifiers
                ->map(fn (ClientIdentifier $identifier): array => [
                    'id' => $identifier->id,
                    'type' => $identifier->type->value,
                    'type_label' => $identifier->type->label(),
                    'masked' => IdentifierMask::mask($identifier->display_value),
                ])
                ->values()
                ->all(),
            'updated_at' => $profile->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function clientDetail(ClientProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'display_name' => $profile->display_name,
            'category' => $profile->category->value,
            'category_label' => $profile->category->label(),
            'verification_state' => $profile->verification_state->value,
            'verification_label' => $profile->verification_state->label(),
            'source_reference' => $profile->source_reference,
            'verified_by' => $profile->verifiedBy?->name,
            'verified_at' => $profile->verified_at?->toIso8601String(),
            'created_by' => $profile->createdBy->name,
            'created_at' => $profile->created_at?->toIso8601String(),
            'updated_at' => $profile->updated_at?->toIso8601String(),
            'version' => $profile->version,
            'identifiers' => $profile->identifiers
                ->map(fn (ClientIdentifier $identifier): array => [
                    'id' => $identifier->id,
                    'type' => $identifier->type->value,
                    'type_label' => $identifier->type->label(),
                    'display_value' => $identifier->display_value,
                    'current' => $identifier->isCurrent(),
                    'added_at' => $identifier->added_at->toIso8601String(),
                    'added_by' => $identifier->addedBy->name,
                    'retired_at' => $identifier->retired_at?->toIso8601String(),
                    'retired_by' => $identifier->retiredBy?->name,
                    'retire_reason' => $identifier->retire_reason,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function revisionSummary(ClientProfileRevision $revision): array
    {
        return [
            'revision_number' => $revision->revision_number,
            'action' => $revision->action->value,
            'action_label' => $revision->action->label(),
            'actor' => $revision->actor->name,
            'occurred_at' => $revision->occurred_at->toIso8601String(),
            'reason' => $revision->reason,
            'changes' => $revision->changes,
        ];
    }

    /**
     * Choices for forms.
     *
     * @return array<string, mixed>
     */
    protected function clientOptions(): array
    {
        return [
            'categories' => array_map(fn (ClientCategory $category): array => [
                'value' => $category->value,
                'label' => $category->label(),
            ], ClientCategory::cases()),
            'states' => array_map(fn (VerificationState $state): array => [
                'value' => $state->value,
                'label' => $state->label(),
            ], VerificationState::cases()),
            'identifierTypes' => array_map(fn (IdentifierType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ], IdentifierType::cases()),
            'identifierRule' => IdentifierNormalizer::DESCRIPTION,
        ];
    }
}
