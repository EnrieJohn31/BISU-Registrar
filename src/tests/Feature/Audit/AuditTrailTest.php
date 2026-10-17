<?php

namespace Tests\Feature\Audit;

use App\Actions\Accounts\AssignRole;
use App\Enums\AuditAction;
use App\Enums\OfficeCode;
use App\Enums\Role;
use App\Models\AuditEvent;
use App\Models\Office;
use App\Models\User;
use App\Support\Audit\AuditRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use LogicException;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_events_cannot_be_changed(): void
    {
        $event = app(AuditRecorder::class)->record(AuditAction::UserUpdated, reason: 'Original');

        $this->expectException(LogicException::class);

        $event->update(['reason' => 'Rewritten']);
    }

    public function test_audit_events_cannot_be_deleted(): void
    {
        $event = app(AuditRecorder::class)->record(AuditAction::UserUpdated);

        $this->expectException(LogicException::class);

        $event->delete();
    }

    public function test_secrets_are_never_written(): void
    {
        $event = app(AuditRecorder::class)->record(
            AuditAction::UserUpdated,
            changes: [
                'name' => ['old' => 'A', 'new' => 'B'],
                'password' => ['old' => 'x', 'new' => 'y'],
                'remember_token' => ['old' => 'x', 'new' => 'y'],
            ],
            context: [
                'token' => 'secret',
                'nested' => ['two_factor_secret' => 'secret', 'kept' => 1],
            ],
        );

        $stored = AuditEvent::query()->findOrFail($event->id);

        $this->assertEquals(['name' => ['old' => 'A', 'new' => 'B']], $stored->changes);
        $this->assertEquals(['nested' => ['kept' => 1]], $stored->context);
    }

    public function test_system_actions_have_no_actor(): void
    {
        $event = app(AuditRecorder::class)->record(AuditAction::RoleAssigned, system: true);

        $this->assertSame('system', $event->actor_type);
        $this->assertNull($event->actor_user_id);
    }

    public function test_administrators_see_every_office_and_supervisors_see_their_own(): void
    {
        $admin = User::factory()->withTwoFactor()->withRole(Role::SystemAdmin)->create();
        $registrarSupervisor = User::factory()->withTwoFactor()->withRole(Role::OfficeSupervisor, OfficeCode::Registrar)->create();

        $assign = app(AssignRole::class);
        $assign->handle($admin, User::factory()->create(), Role::IntakeStaff, Office::forCode(OfficeCode::Registrar), 'Registrar hire');
        $assign->handle($admin, User::factory()->create(), Role::Cashier, Office::forCode(OfficeCode::Cashier), 'Cashier hire');
        app(AuditRecorder::class)->record(AuditAction::UserUpdated, reason: 'Campus-wide account change', actor: $admin);

        $this->actingAs($admin)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/audit/Index')
                ->where('scope', 'all')
                ->has('events.data', 3));

        $this->actingAs($registrarSupervisor)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scope', 'office')
                ->has('events.data', 1)
                ->where('events.data.0.office', 'REGISTRAR')
                ->where('events.data.0.reason', 'Registrar hire'));
    }

    public function test_the_audit_trail_can_be_filtered(): void
    {
        $admin = User::factory()->withTwoFactor()->withRole(Role::SystemAdmin)->create();

        app(AuditRecorder::class)->record(AuditAction::UserUpdated, actor: $admin);
        app(AuditRecorder::class)->record(AuditAction::UserDeactivated, actor: $admin);

        $this->actingAs($admin)
            ->get(route('admin.audit.index', ['action' => 'user.deactivated']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('events.data', 1)
                ->where('events.data.0.action', 'user.deactivated'));
    }

    public function test_clients_and_unrelated_staff_cannot_open_the_audit_trail(): void
    {
        $client = User::factory()->create();
        $intake = User::factory()->withRole(Role::IntakeStaff)->create();

        $this->actingAs($client)->get(route('admin.audit.index'))->assertForbidden();
        $this->actingAs($intake)->get(route('admin.audit.index'))->assertForbidden();
    }
}
