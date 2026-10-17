<?php

namespace Database\Factories;

use App\Enums\OfficeCode;
use App\Enums\Role;
use App\Models\Office;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Give the user an active role. Office-scoped roles default to their first
     * allowed office. Written directly (no audit event) because it is a fixture.
     */
    public function withRole(Role $role, ?OfficeCode $office = null): static
    {
        return $this->afterCreating(function (User $user) use ($role, $office): void {
            $office ??= $role->isCampusWide() ? null : $role->allowedOffices()[0];

            $user->roleAssignments()->create([
                'role' => $role,
                'office_id' => $office === null ? null : Office::forCode($office)->id,
                'assigned_by_id' => null,
                'assigned_at' => now(),
                'assignment_reason' => 'Test fixture',
            ]);

            $user->forgetRoles();
        });
    }

    /**
     * Indicate that the account was deactivated.
     */
    public function deactivated(): static
    {
        return $this->state(fn (array $attributes) => [
            'deactivated_at' => now(),
            'deactivation_reason' => 'Test fixture',
        ]);
    }

    /**
     * Indicate that the user must replace a temporary password.
     */
    public function withTemporaryPassword(?CarbonInterface $expiresAt = null): static
    {
        return $this->state(fn (array $attributes) => [
            'must_change_password' => true,
            'temporary_password_expires_at' => $expiresAt ?? now()->addHours(72),
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
