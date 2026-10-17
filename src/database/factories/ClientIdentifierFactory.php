<?php

namespace Database\Factories;

use App\Enums\IdentifierType;
use App\Models\ClientIdentifier;
use App\Models\ClientProfile;
use App\Models\User;
use App\Support\Clients\IdentifierNormalizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientIdentifier>
 */
class ClientIdentifierFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $value = 'DEMO-X-'.fake()->unique()->numerify('######');

        return [
            'client_profile_id' => ClientProfile::factory(),
            'type' => IdentifierType::Student,
            'display_value' => $value,
            'canonical_value' => strtoupper($value),
            'normalization_version' => IdentifierNormalizer::VERSION,
            'added_by_id' => User::factory(),
            'added_at' => now(),
        ];
    }

    public function retired(?User $by = null): static
    {
        return $this->state(fn (array $attributes) => [
            'retired_at' => now(),
            'retired_by_id' => $by->id ?? $attributes['added_by_id'],
            'retire_reason' => 'Test fixture',
        ]);
    }
}
