<?php

namespace Database\Factories;

use App\Enums\ClientCategory;
use App\Enums\IdentifierType;
use App\Enums\OfficeCode;
use App\Enums\VerificationState;
use App\Models\ClientIdentifier;
use App\Models\ClientProfile;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Synthetic fixtures only: never real client records.
 *
 * @extends Factory<ClientProfile>
 */
class ClientProfileFactory extends Factory
{
    private static int $sequence = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'office_id' => Office::forCode(OfficeCode::Registrar)->id,
            'display_name' => fake()->name().' (Demo)',
            'category' => ClientCategory::Unknown,
            'verification_state' => VerificationState::Unverified,
            'source_reference' => 'Test fixture',
            'created_by_id' => User::factory(),
            'version' => 1,
        ];
    }

    /**
     * Give the profile a current ID. Without a value, a unique demo ID is made.
     */
    public function withIdentifier(IdentifierType $type = IdentifierType::Student, ?string $value = null): static
    {
        return $this->afterCreating(function (ClientProfile $profile) use ($type, $value): void {
            $value ??= 'DEMO-'.strtoupper($type->value[0]).'-'.str_pad((string) ++self::$sequence, 6, '0', STR_PAD_LEFT);

            ClientIdentifier::factory()->create([
                'client_profile_id' => $profile->id,
                'type' => $type,
                'display_value' => $value,
                'canonical_value' => strtoupper($value),
                'added_by_id' => $profile->created_by_id,
            ]);
        });
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_state' => VerificationState::Verified,
            'verified_by_id' => $attributes['created_by_id'],
            'verified_at' => now(),
        ]);
    }

    public function needsReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_state' => VerificationState::NeedsReview,
        ]);
    }

    public function category(ClientCategory $category): static
    {
        return $this->state(fn (array $attributes) => ['category' => $category]);
    }
}
