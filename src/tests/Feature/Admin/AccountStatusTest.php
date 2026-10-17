<?php

namespace Tests\Feature\Admin;

use App\Actions\Accounts\DeactivateUser;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Passkeys;
use Tests\TestCase;

class AccountStatusTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->withTwoFactor()->withRole(Role::SystemAdmin)->create();
    }

    public function test_deactivation_keeps_the_account_and_records_who_and_why(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->withRole(Role::IntakeStaff)->create(['remember_token' => 'old-token']);

        $this->actingAs($admin)->post(route('admin.users.deactivate', $staff), [
            'reason' => 'Left the office',
        ])->assertRedirect(route('admin.users.show', $staff));

        $staff->refresh();

        $this->assertTrue($staff->isDeactivated());
        $this->assertSame($admin->id, $staff->deactivated_by_id);
        $this->assertSame('Left the office', $staff->deactivation_reason);
        $this->assertNotSame('old-token', $staff->remember_token);
        $this->assertSame(1, $staff->roleAssignments()->whereNull('revoked_at')->count(), 'Role history is kept.');
        $this->assertSame([], $staff->permissions(), 'A deactivated account holds no permissions.');
        $this->assertDatabaseHas('audit_events', ['action' => 'user.deactivated', 'reason' => 'Left the office']);
    }

    public function test_deactivation_ends_stored_sessions(): void
    {
        config(['session.driver' => 'database']);

        $admin = $this->admin();
        $staff = User::factory()->withRole(Role::IntakeStaff)->create();

        DB::table('sessions')->insert([
            'id' => 'staff-session',
            'user_id' => $staff->id,
            'ip_address' => '10.0.0.5',
            'user_agent' => 'test',
            'payload' => '',
            'last_activity' => now()->getTimestamp(),
        ]);

        app(DeactivateUser::class)->handle($admin, $staff, 'Security incident');

        $this->assertDatabaseMissing('sessions', ['user_id' => $staff->id]);
    }

    public function test_an_open_session_of_a_deactivated_account_ends_on_its_next_request(): void
    {
        $user = User::factory()->deactivated()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();
        $this->assertDatabaseHas('audit_events', ['action' => 'auth.denied', 'actor_user_id' => $user->id, 'outcome' => 'denied']);
    }

    public function test_a_deactivated_account_cannot_sign_in_with_a_password(): void
    {
        $user = User::factory()->deactivated()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => __('This account is not active. Please contact the Registrar\'s Office.')]);

        $this->assertGuest();
    }

    public function test_a_wrong_password_does_not_reveal_that_an_account_is_deactivated(): void
    {
        $user = User::factory()->deactivated()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);
    }

    public function test_a_deactivated_account_with_two_factor_never_reaches_the_challenge(): void
    {
        $user = User::factory()->withTwoFactor()->deactivated()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertNull(session('login.id'));
    }

    public function test_a_deactivated_account_cannot_sign_in_with_a_passkey(): void
    {
        $active = User::factory()->create();
        $deactivated = User::factory()->deactivated()->create();

        $this->assertTrue(Passkeys::allowsLogin(request(), (new Passkey)->setRelation('user', $active)));
        $this->assertFalse(Passkeys::allowsLogin(request(), (new Passkey)->setRelation('user', $deactivated)));
    }

    public function test_reactivation_restores_access(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->withRole(Role::IntakeStaff)->deactivated()->create();

        $this->actingAs($admin)->post(route('admin.users.reactivate', $staff), [
            'reason' => 'Returned from leave',
        ])->assertRedirect(route('admin.users.show', $staff));

        $this->assertFalse($staff->fresh()?->isDeactivated());
        $this->assertDatabaseHas('audit_events', ['action' => 'user.reactivated', 'reason' => 'Returned from leave']);

        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => $staff->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($staff);
    }

    public function test_administrators_cannot_deactivate_themselves(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.users.deactivate', $admin), [
            'reason' => 'Self',
        ])->assertForbidden();

        $this->assertFalse($admin->fresh()?->isDeactivated());
    }

    public function test_administrators_can_issue_a_temporary_password(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->withRole(Role::IntakeStaff)->create();

        $this->actingAs($admin)->post(route('admin.users.temporary-password', $staff), [
            'reason' => 'Forgot password, identity checked at the counter',
        ])->assertOk()->assertViewHas('page', fn (array $page): bool => $page['component'] === 'admin/users/TemporaryPassword');

        $staff->refresh();

        $this->assertTrue($staff->must_change_password);
        $this->assertDatabaseHas('audit_events', ['action' => 'user.temporary_password_issued']);

        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => $staff->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_administrators_can_reset_two_factor(): void
    {
        $admin = $this->admin();
        $cashier = User::factory()->withTwoFactor()->withRole(Role::Cashier)->create();

        $this->actingAs($admin)->delete(route('admin.users.two-factor.reset', $cashier), [
            'reason' => 'Lost phone, identity checked in person',
        ])->assertRedirect(route('admin.users.show', $cashier));

        $this->assertNull($cashier->fresh()?->two_factor_secret);
        $this->assertDatabaseHas('audit_events', ['action' => 'user.two_factor_reset']);
    }
}
