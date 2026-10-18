<?php

namespace Tests\Feature\Registrar;

use App\Enums\OfficeCode;
use App\Enums\Role;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClientAccessTest extends TestCase
{
    use RefreshDatabase;

    private function staff(Role $role, ?OfficeCode $office = null): User
    {
        $factory = User::factory()->withRole($role, $office);

        return $role->requiresTwoFactor() ? $factory->withTwoFactor()->create() : $factory->create();
    }

    /**
     * @return array<string, array{0: Role, 1: OfficeCode|null, 2: bool, 3: bool}>
     */
    public static function roles(): array
    {
        // role, office, may view client records, may change them
        return [
            'intake staff' => [Role::IntakeStaff, null, true, false],
            'registrar processor' => [Role::RegistrarProcessor, null, true, true],
            'registrar approver' => [Role::RegistrarApprover, null, true, false],
            'release officer' => [Role::ReleaseOfficer, null, true, false],
            'cashier' => [Role::Cashier, null, false, false],
            'registrar supervisor' => [Role::OfficeSupervisor, OfficeCode::Registrar, false, false],
            'cashier supervisor' => [Role::OfficeSupervisor, OfficeCode::Cashier, false, false],
            'report exporter' => [Role::ReportExporter, OfficeCode::Registrar, false, false],
            'supplier staff' => [Role::SupplierStaff, null, false, false],
            'supplier supervisor' => [Role::SupplierSupervisor, null, false, false],
            'system administrator' => [Role::SystemAdmin, null, false, false],
        ];
    }

    #[DataProvider('roles')]
    public function test_each_role_gets_only_the_client_access_it_was_approved_for(Role $role, ?OfficeCode $office, bool $mayView, bool $mayChange): void
    {
        $user = $this->staff($role, $office);
        $profile = ClientProfile::factory()->withIdentifier()->create();

        $viewStatus = $mayView ? 200 : 403;

        $this->actingAs($user)->get(route('registrar.clients.index'))->assertStatus($viewStatus);
        $this->actingAs($user)->get(route('registrar.clients.show', $profile))->assertStatus($viewStatus);
        $this->actingAs($user)->get(route('registrar.clients.show', ['clientProfile' => $profile, 'history_page' => 2]))->assertStatus($viewStatus);

        $changeStatus = $mayChange ? 200 : 403;

        $this->actingAs($user)->get(route('registrar.clients.create'))->assertStatus($changeStatus);

        // Changes are refused before any validation or action runs.
        if (! $mayChange) {
            $payload = ['reason' => 'Testing access', 'version' => $profile->version];

            $this->actingAs($user)->post(route('registrar.clients.store'), $payload)->assertForbidden();
            $this->actingAs($user)->patch(route('registrar.clients.update', $profile), $payload)->assertForbidden();
            $this->actingAs($user)->post(route('registrar.clients.verify', $profile), $payload)->assertForbidden();
            $this->actingAs($user)->post(route('registrar.clients.flag', $profile), $payload)->assertForbidden();
            $this->actingAs($user)->post(route('registrar.clients.identifiers.store', $profile), $payload)->assertForbidden();
        }
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $profile = ClientProfile::factory()->create();

        $this->get(route('registrar.clients.index'))->assertRedirect(route('login'));
        $this->get(route('registrar.clients.show', $profile))->assertRedirect(route('login'));
        $this->post(route('registrar.clients.verify', $profile))->assertRedirect(route('login'));
    }

    public function test_a_client_account_with_no_staff_role_is_refused(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)->get(route('registrar.clients.index'))->assertForbidden();
    }

    public function test_a_deactivated_staff_member_loses_access(): void
    {
        $processor = User::factory()->withRole(Role::RegistrarProcessor)->deactivated()->create();

        $this->actingAs($processor)->get(route('registrar.clients.index'))->assertRedirect(route('login'));
    }

    public function test_refused_requests_are_audited(): void
    {
        $cashier = $this->staff(Role::Cashier);

        $this->actingAs($cashier)->get(route('registrar.clients.index'))->assertForbidden();

        $this->assertDatabaseHas('audit_events', [
            'action' => 'auth.denied',
            'outcome' => 'denied',
            'actor_user_id' => $cashier->id,
        ]);
    }

    public function test_only_the_registrar_processor_sees_change_actions(): void
    {
        $profile = ClientProfile::factory()->withIdentifier()->create();

        $this->actingAs($this->staff(Role::RegistrarProcessor))
            ->get(route('registrar.clients.show', $profile))
            ->assertInertia(fn ($page) => $page->where('can.manage', true));

        $this->actingAs($this->staff(Role::IntakeStaff))
            ->get(route('registrar.clients.show', $profile))
            ->assertInertia(fn ($page) => $page->where('can.manage', false));
    }
}
