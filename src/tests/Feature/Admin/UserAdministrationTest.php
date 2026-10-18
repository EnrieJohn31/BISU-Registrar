<?php

namespace Tests\Feature\Admin;

use App\Enums\OfficeCode;
use App\Enums\Permission;
use App\Enums\Role;
use App\Models\AuditEvent;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->withTwoFactor()->withRole(Role::SystemAdmin)->create();
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_administrators_can_list_and_filter_users(): void
    {
        $admin = $this->admin();
        User::factory()->withTwoFactor()->withRole(Role::Cashier)->create();
        User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/Index')
                ->has('users.data', 3));

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['status' => 'clients']))
            ->assertInertia(fn (Assert $page) => $page->has('users.data', 1));

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'cashier']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.roles.0.role', 'cashier'));
    }

    public function test_users_without_the_permission_are_refused_and_the_refusal_is_audited(): void
    {
        $cashier = User::factory()->withTwoFactor()->withRole(Role::Cashier)->create();
        $client = User::factory()->create();

        $this->actingAs($cashier)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($client)->get(route('admin.users.create'))->assertForbidden();

        $this->assertDatabaseHas('audit_events', [
            'action' => 'auth.denied',
            'outcome' => 'denied',
            'actor_user_id' => $cashier->id,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'action' => 'auth.denied',
            'actor_user_id' => $client->id,
        ]);
    }

    public function test_the_system_administrator_has_no_business_permissions(): void
    {
        $admin = $this->admin();

        $this->assertTrue($admin->hasPermission(Permission::UsersManage));
        $this->assertFalse($admin->hasPermission(Permission::PaymentsVerify));
        $this->assertFalse($admin->hasPermission(Permission::DocumentsApprove));
        $this->assertFalse($admin->hasPermission(Permission::ReleasesRecord));
    }

    public function test_administrators_can_provision_a_staff_account(): void
    {
        $admin = $this->admin();
        $registrar = Office::forCode(OfficeCode::Registrar);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Maria Santos',
            'email' => 'Maria.Santos@bisu.test',
            'office_id' => $registrar->id,
            'roles' => ['registrar_processor', 'intake_staff'],
            'reason' => 'New Registrar staff member',
        ]);

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/TemporaryPassword')
            ->where('user.email', 'maria.santos@bisu.test')
            ->where('context', 'provisioned')
            ->has('temporaryPassword'));

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $user = User::query()->where('email', 'maria.santos@bisu.test')->firstOrFail();

        $this->assertTrue($user->must_change_password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->temporary_password_expires_at?->isFuture());
        $this->assertTrue($user->hasRole(Role::RegistrarProcessor, $registrar));
        $this->assertTrue($user->hasRole(Role::IntakeStaff, $registrar));

        $events = AuditEvent::query()->where('subject_type', 'user')->where('subject_id', (string) $user->id)->get();

        $this->assertEqualsCanonicalizing(['user.provisioned', 'role.assigned', 'role.assigned'], $events->pluck('action')->all());
        $this->assertCount(1, $events->pluck('correlation_id')->unique(), 'One action should share one correlation id.');
        $this->assertSame('New Registrar staff member', $events->firstWhere('action', 'user.provisioned')?->reason);
    }

    public function test_the_temporary_password_signs_in_and_forces_a_new_password(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Jose Cruz',
            'email' => 'jose@bisu.test',
            'office_id' => Office::forCode(OfficeCode::Registrar)->id,
            'roles' => ['intake_staff'],
            'reason' => 'New intake staff',
        ]);

        /** @var array{props: array{temporaryPassword: string}} $page */
        $page = $response->viewData('page');
        $temporaryPassword = $page['props']['temporaryPassword'];

        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => 'jose@bisu.test', 'password' => $temporaryPassword]);

        $this->assertAuthenticated();
        $this->get(route('dashboard'))->assertRedirect(route('password.change.edit'));
    }

    public function test_roles_must_be_valid_for_the_selected_office(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Wrong Office',
            'email' => 'wrong@bisu.test',
            'office_id' => Office::forCode(OfficeCode::Registrar)->id,
            'roles' => ['cashier'],
            'reason' => 'Testing',
        ])->assertSessionHasErrors('roles');

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'No Office',
            'email' => 'no-office@bisu.test',
            'roles' => ['registrar_processor'],
            'reason' => 'Testing',
        ])->assertSessionHasErrors('office_id');

        $this->assertDatabaseMissing('users', ['email' => 'wrong@bisu.test']);
        $this->assertDatabaseMissing('users', ['email' => 'no-office@bisu.test']);
    }

    public function test_a_reason_is_required(): void
    {
        $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name' => 'No Reason',
            'email' => 'no-reason@bisu.test',
            'roles' => ['system_admin'],
            'reason' => '',
        ])->assertSessionHasErrors('reason');
    }

    public function test_administrators_can_correct_details_and_the_change_is_audited(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->withRole(Role::IntakeStaff)->create(['name' => 'Old Name']);

        $this->actingAs($admin)->patch(route('admin.users.update', $staff), [
            'name' => 'New Name',
            'email' => $staff->email,
            'reason' => 'Spelling correction',
        ])->assertRedirect(route('admin.users.show', $staff));

        $this->assertSame('New Name', $staff->fresh()?->name);

        $event = AuditEvent::query()->where('action', 'user.updated')->firstOrFail();

        $this->assertEquals(['name' => ['old' => 'Old Name', 'new' => 'New Name']], $event->changes);
        $this->assertSame('Spelling correction', $event->reason);
    }

    public function test_administrators_can_view_an_account(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->withRole(Role::IntakeStaff)->create();

        $this->actingAs($admin)
            ->get(route('admin.users.show', $staff))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/Show')
                ->where('user.id', $staff->id)
                ->has('history', 1)
                ->where('can.manageRoles', true));

        $this->actingAs($admin)
            ->get(route('admin.users.show', $admin))
            ->assertInertia(fn (Assert $page) => $page->where('can.manageRoles', false)->where('can.changeStatus', false));
    }
}
