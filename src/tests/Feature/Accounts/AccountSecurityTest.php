<?php

namespace Tests\Feature\Accounts;

use App\Enums\Role;
use App\Http\Middleware\StaffIdleTimeout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_temporary_password_must_be_replaced_before_anything_else(): void
    {
        $user = User::factory()->withRole(Role::IntakeStaff)->withTemporaryPassword()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('password.change.edit'));
        $this->actingAs($user)->get(route('profile.edit'))->assertRedirect(route('password.change.edit'));

        $this->actingAs($user)
            ->get(route('password.change.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('auth/ChangePassword'));
    }

    public function test_setting_a_new_password_completes_the_first_sign_in(): void
    {
        $user = User::factory()->withRole(Role::IntakeStaff)->withTemporaryPassword()->create();

        $this->actingAs($user)->put(route('password.change.update'), [
            'password' => 'a-new-secret-passphrase',
            'password_confirmation' => 'a-new-secret-passphrase',
        ])->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertFalse($user->must_change_password);
        $this->assertNull($user->temporary_password_expires_at);
        $this->assertTrue(Hash::check('a-new-secret-passphrase', $user->password));
        $this->assertDatabaseHas('audit_events', ['action' => 'user.password_changed', 'actor_user_id' => $user->id]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_the_temporary_password_cannot_be_reused(): void
    {
        $user = User::factory()->withTemporaryPassword()->create();

        $this->actingAs($user)->put(route('password.change.update'), [
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');

        $this->assertTrue($user->fresh()?->must_change_password);
    }

    public function test_an_expired_temporary_password_cannot_sign_in(): void
    {
        $user = User::factory()->withTemporaryPassword(now()->subHour())->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => __('Your temporary password has expired. Ask a system administrator for a new one.')]);

        $this->assertGuest();
    }

    public function test_an_open_session_with_an_expired_temporary_password_is_ended(): void
    {
        $user = User::factory()->withTemporaryPassword(now()->subHour())->create();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_sign_out_works_while_a_password_change_is_pending(): void
    {
        $user = User::factory()->withTemporaryPassword()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_sensitive_roles_must_set_up_two_factor_first(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();

        $this->actingAs($cashier)->get(route('dashboard'))->assertRedirect(route('security.edit'));

        $this->actingAs($cashier)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.edit'))
            ->assertOk();
    }

    public function test_sensitive_roles_with_two_factor_and_other_roles_are_not_redirected(): void
    {
        $cashier = User::factory()->withTwoFactor()->withRole(Role::Cashier)->create();
        $intake = User::factory()->withRole(Role::IntakeStaff)->create();

        $this->actingAs($cashier)->get(route('dashboard'))->assertOk();
        $this->actingAs($intake)->get(route('dashboard'))->assertOk();
    }

    public function test_staff_are_signed_out_after_the_idle_limit(): void
    {
        $staff = User::factory()->withRole(Role::IntakeStaff)->create();

        $this->actingAs($staff)
            ->withSession([StaffIdleTimeout::SESSION_KEY => now()->subMinutes(31)->getTimestamp()])
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();
    }

    public function test_active_staff_and_clients_stay_signed_in(): void
    {
        $staff = User::factory()->withRole(Role::IntakeStaff)->create();
        $client = User::factory()->create();

        $this->actingAs($staff)
            ->withSession([StaffIdleTimeout::SESSION_KEY => now()->subMinutes(5)->getTimestamp()])
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($client)
            ->withSession([StaffIdleTimeout::SESSION_KEY => now()->subHours(3)->getTimestamp()])
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_staff_sessions_are_not_restored_from_a_remember_me_cookie(): void
    {
        $staff = User::factory()->withRole(Role::IntakeStaff)->create(['remember_token' => 'remember-token']);

        $this->withCookie(
            Auth::guard('web')->getRecallerName(),
            $staff->id.'|remember-token|'.$staff->getAuthPassword(),
        )->get(route('dashboard'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_shared_page_props_use_an_allow_list(): void
    {
        $staff = User::factory()->withRole(Role::IntakeStaff)->create(['deactivation_reason' => null]);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.user.id', $staff->id)
                ->missing('auth.user.password')
                ->missing('auth.user.remember_token')
                ->missing('auth.user.deactivation_reason')
                ->missing('auth.user.two_factor_secret')
                ->where('auth.roles.0.role', 'intake_staff')
                ->where('auth.permissions', fn ($permissions): bool => collect($permissions)->contains('requests.intake')));
    }
}
