<?php

namespace Tests\Feature;

use App\Enums\OfficeCode;
use App\Enums\Role;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function staff(Role $role, ?OfficeCode $office = null): User
    {
        $factory = User::factory()->withRole($role, $office);

        return $role->requiresTwoFactor() ? $factory->withTwoFactor()->create() : $factory->create();
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_client_accounts_get_no_staff_shortcuts_or_counts(): void
    {
        ClientProfile::factory()->withIdentifier()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('access', [
                    'clients' => false,
                    'createClient' => false,
                    'users' => false,
                    'createUser' => false,
                    'audit' => false,
                ])
                ->where('clientCounts', null));
    }

    public function test_registrar_staff_see_client_totals_by_verification_state(): void
    {
        ClientProfile::factory()->count(3)->withIdentifier()->create();
        ClientProfile::factory()->count(2)->withIdentifier()->verified()->create();
        ClientProfile::factory()->withIdentifier()->needsReview()->create();
        $stale = ClientProfile::factory()->withIdentifier()->create();
        $stale->forceFill(['updated_at' => now()->subDays(8)])->saveQuietly();

        $this->actingAs($this->staff(Role::RegistrarProcessor))
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('access.clients', true)
                ->where('access.createClient', true)
                ->where('access.users', false)
                ->where('clientCounts', [
                    'total' => 7,
                    'verified' => 2,
                    'unverified' => 4,
                    'needs_review' => 1,
                    'updated_this_week' => 6,
                ]));
    }

    public function test_view_only_client_access_offers_no_create_shortcut(): void
    {
        $this->actingAs($this->staff(Role::IntakeStaff))
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('access.clients', true)
                ->where('access.createClient', false)
                ->where('clientCounts.total', 0));
    }

    public function test_staff_outside_the_registrar_office_get_no_client_counts(): void
    {
        ClientProfile::factory()->withIdentifier()->create();

        foreach ([[Role::Cashier, null], [Role::OfficeSupervisor, OfficeCode::Cashier]] as [$role, $office]) {
            $this->actingAs($this->staff($role, $office))
                ->get(route('dashboard'))
                ->assertInertia(fn (Assert $page) => $page
                    ->where('access.clients', false)
                    ->where('access.createClient', false)
                    ->where('clientCounts', null));
        }
    }

    public function test_administrators_get_account_and_audit_shortcuts_without_client_data(): void
    {
        $this->actingAs($this->staff(Role::SystemAdmin))
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('access.users', true)
                ->where('access.createUser', true)
                ->where('access.audit', true)
                ->where('access.clients', false)
                ->where('clientCounts', null));
    }
}
