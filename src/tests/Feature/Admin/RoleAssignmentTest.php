<?php

namespace Tests\Feature\Admin;

use App\Actions\Accounts\RevokeRole;
use App\Enums\OfficeCode;
use App\Enums\Permission;
use App\Enums\Role;
use App\Models\AuditEvent;
use App\Models\Office;
use App\Models\User;
use App\Models\UserRole;
use App\Support\Accounts\AdministratorGuard;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->withTwoFactor()->withRole(Role::SystemAdmin)->create();
    }

    public function test_assigning_a_role_records_history_and_an_audit_event(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create();
        $cashierOffice = Office::forCode(OfficeCode::Cashier);

        $this->actingAs($admin)->post(route('admin.users.roles.store', $staff), [
            'role' => 'cashier',
            'office_id' => $cashierOffice->id,
            'reason' => 'Appointed cashier',
        ])->assertRedirect(route('admin.users.show', $staff));

        $assignment = UserRole::query()->where('user_id', $staff->id)->sole();

        $this->assertSame(Role::Cashier, $assignment->role);
        $this->assertSame($cashierOffice->id, $assignment->office_id);
        $this->assertSame($admin->id, $assignment->assigned_by_id);
        $this->assertSame('Appointed cashier', $assignment->assignment_reason);

        $event = AuditEvent::query()->where('action', 'role.assigned')->sole();

        $this->assertSame($cashierOffice->id, $event->office_id);
        $this->assertEquals(['role' => 'cashier', 'office' => 'CASHIER', 'assignment_id' => $assignment->id], $event->context);
    }

    public function test_a_role_cannot_be_held_twice_in_the_same_office(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->withRole(Role::IntakeStaff)->create();

        $this->actingAs($admin)->post(route('admin.users.roles.store', $staff), [
            'role' => 'intake_staff',
            'office_id' => Office::forCode(OfficeCode::Registrar)->id,
            'reason' => 'Duplicate',
        ])->assertSessionHasErrors('role');

        $this->assertSame(1, UserRole::query()->where('user_id', $staff->id)->count());
    }

    public function test_the_database_rejects_a_duplicate_active_assignment(): void
    {
        $staff = User::factory()->withRole(Role::IntakeStaff)->create();

        $this->expectException(UniqueConstraintViolationException::class);

        $staff->roleAssignments()->create([
            'role' => Role::IntakeStaff,
            'office_id' => Office::forCode(OfficeCode::Registrar)->id,
            'assigned_at' => now(),
            'assignment_reason' => 'Bypassing the action',
        ]);
    }

    public function test_a_role_must_be_valid_for_the_office(): void
    {
        $this->actingAs($this->admin())->post(route('admin.users.roles.store', User::factory()->create()), [
            'role' => 'cashier',
            'office_id' => Office::forCode(OfficeCode::Registrar)->id,
            'reason' => 'Wrong office',
        ])->assertSessionHasErrors('office_id');
    }

    public function test_nobody_can_change_their_own_roles(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.users.roles.store', $admin), [
            'role' => 'cashier',
            'office_id' => Office::forCode(OfficeCode::Cashier)->id,
            'reason' => 'Self assignment',
        ])->assertForbidden();

        $this->assertFalse($admin->fresh()?->hasRole(Role::Cashier));
        $this->assertDatabaseHas('audit_events', ['action' => 'auth.denied', 'actor_user_id' => $admin->id]);
    }

    public function test_revoking_a_role_keeps_history_and_removes_access_on_the_next_request(): void
    {
        $admin = $this->admin();
        $supervisor = User::factory()->withTwoFactor()->withRole(Role::OfficeSupervisor, OfficeCode::Registrar)->create();

        $this->actingAs($supervisor)->get(route('admin.audit.index'))->assertOk();

        $assignment = $supervisor->roleAssignments()->sole();

        $this->actingAs($admin)->delete(route('admin.users.roles.destroy', [$supervisor, $assignment]), [
            'reason' => 'Transferred to another campus',
        ])->assertRedirect(route('admin.users.show', $supervisor));

        $assignment->refresh();
        $this->assertNotNull($assignment->revoked_at);
        $this->assertSame($admin->id, $assignment->revoked_by_id);
        $this->assertSame('Transferred to another campus', $assignment->revoke_reason);
        $this->assertDatabaseHas('audit_events', ['action' => 'role.revoked', 'reason' => 'Transferred to another campus']);

        // A real next request loads the user fresh from the session.
        $this->actingAs($supervisor->fresh() ?? $supervisor)->get(route('admin.audit.index'))->assertForbidden();
    }

    public function test_an_assignment_must_belong_to_the_user_in_the_url(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->withRole(Role::IntakeStaff)->create();
        $other = User::factory()->withRole(Role::IntakeStaff)->create();

        $this->actingAs($admin)->delete(route('admin.users.roles.destroy', [$staff, $other->roleAssignments()->sole()]), [
            'reason' => 'Mismatched',
        ])->assertNotFound();
    }

    public function test_an_administrator_whose_access_was_revoked_cannot_act_with_a_stale_session(): void
    {
        $first = $this->admin();
        $second = $this->admin();

        // Load $second's roles now, as a request that started before the revoke would have.
        $this->assertTrue($second->hasPermission(Permission::RolesAssign));

        $this->actingAs($first)->delete(route('admin.users.roles.destroy', [$second, $second->roleAssignments()->sole()]), [
            'reason' => 'Role no longer needed',
        ])->assertRedirect();

        // The stale in-memory role list still says administrator.
        $this->assertTrue($second->hasPermission(Permission::RolesAssign));

        try {
            app(RevokeRole::class)->handle($second, $first->roleAssignments()->sole(), 'Racing revoke');
            $this->fail('A revoked administrator was able to revoke another administrator.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('administrator access has changed', $exception->getMessage());
        }

        $this->assertTrue($first->fresh()?->hasRole(Role::SystemAdmin));
    }

    public function test_at_least_one_active_administrator_must_remain(): void
    {
        $only = $this->admin();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('At least one active system administrator must remain.');

        AdministratorGuard::ensureAnotherAdministratorRemains($only, $only, 'reason');
    }
}
