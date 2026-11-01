<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use App\Notifications\DevelopmentTwoFactorCode;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class DevelopmentTwoFactorCodeTest extends TestCase
{
    use RefreshDatabase;

    private string $secret;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $this->secret = app(Google2FA::class)->generateSecretKey();

        Notification::fake();
    }

    public function test_challenge_code_is_refused_outside_the_local_environment(): void
    {
        $this->startChallenge($this->userWithTwoFactor());

        $this->postJson(route('dev.two-factor-code.challenge'))->assertNotFound();
        $this->get(route('two-factor.login'))
            ->assertInertia(fn (Assert $page) => $page->where('devTwoFactorInboxUrl', null));

        Notification::assertNothingSent();
    }

    public function test_challenge_code_is_refused_when_the_flag_is_off(): void
    {
        $this->useLocalEnvironment();
        config(['bisu.dev_two_factor_email' => false]);

        $this->startChallenge($this->userWithTwoFactor());

        $this->postJson(route('dev.two-factor-code.challenge'))->assertNotFound();

        Notification::assertNothingSent();
    }

    public function test_challenge_code_requires_a_pending_sign_in(): void
    {
        $this->useLocalEnvironment();

        $this->postJson(route('dev.two-factor-code.challenge'))->assertNotFound();

        Notification::assertNothingSent();
    }

    public function test_emailed_challenge_code_completes_sign_in_locally(): void
    {
        $this->useLocalEnvironment();
        $user = $this->userWithTwoFactor();

        $this->startChallenge($user);

        $this->get(route('two-factor.login'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/TwoFactorChallenge')
                ->where('devTwoFactorInboxUrl', config('bisu.dev_mail_inbox_url')),
            );

        $this->postJson(route('dev.two-factor-code.challenge'))->assertOk();

        $this->post(route('two-factor.login.store'), ['code' => $this->sentCode($user)]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_emailed_setup_code_confirms_two_factor_for_a_sensitive_role_locally(): void
    {
        $this->useLocalEnvironment();
        $user = User::factory()->withRole(Role::SystemAdmin)->create([
            'two_factor_secret' => encrypt($this->secret),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
        ]);

        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);

        $this->get(route('security.edit'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('devTwoFactorInboxUrl', config('bisu.dev_mail_inbox_url')),
            );

        $this->postJson(route('dev.two-factor-code.setup'))->assertOk();

        $this->post(route('two-factor.confirm'), ['code' => $this->sentCode($user)])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($user->refresh()->two_factor_confirmed_at);
    }

    public function test_setup_code_is_refused_once_two_factor_is_confirmed(): void
    {
        $this->useLocalEnvironment();
        $user = $this->userWithTwoFactor();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson(route('dev.two-factor-code.setup'))
            ->assertNotFound();

        Notification::assertNothingSent();
    }

    /**
     * Laravel skips CSRF only in the testing environment, so switching to local
     * needs it disabled explicitly for these requests.
     */
    private function useLocalEnvironment(): void
    {
        $this->app['env'] = 'local';
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    private function userWithTwoFactor(): User
    {
        return User::factory()->create([
            'two_factor_secret' => encrypt($this->secret),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    private function startChallenge(User $user): void
    {
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('two-factor.login'));
    }

    private function sentCode(User $user): string
    {
        $sent = Notification::sent($user, DevelopmentTwoFactorCode::class);

        $this->assertCount(1, $sent);
        $this->assertTrue(app(Google2FA::class)->verifyKey($this->secret, $sent->first()->code));

        return $sent->first()->code;
    }
}
