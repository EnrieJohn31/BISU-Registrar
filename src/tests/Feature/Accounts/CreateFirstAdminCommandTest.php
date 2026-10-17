<?php

namespace Tests\Feature\Accounts;

use App\Enums\Role;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateFirstAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_first_administrator_with_a_temporary_password(): void
    {
        $this->artisan('bisu:create-admin', ['--name' => 'MIS Officer', '--email' => 'mis@bisu.test'])
            ->expectsOutputToContain('Temporary password (shown once)')
            ->assertSuccessful();

        $admin = User::query()->where('email', 'mis@bisu.test')->firstOrFail();

        $this->assertTrue($admin->hasRole(Role::SystemAdmin));
        $this->assertTrue($admin->must_change_password);

        $events = AuditEvent::query()->where('subject_id', (string) $admin->id)->get();

        $this->assertCount(2, $events);
        $this->assertTrue($events->every(fn (AuditEvent $event): bool => $event->actor_type === 'system'));
    }

    public function test_it_refuses_when_an_active_administrator_exists(): void
    {
        User::factory()->withRole(Role::SystemAdmin)->create();

        $this->artisan('bisu:create-admin', ['--name' => 'Second', '--email' => 'second@bisu.test'])
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'second@bisu.test']);

        $this->artisan('bisu:create-admin', ['--name' => 'Second', '--email' => 'second@bisu.test', '--force' => true])
            ->assertSuccessful();
    }

    public function test_it_validates_the_input(): void
    {
        $this->artisan('bisu:create-admin', ['--name' => 'Bad Email', '--email' => 'not-an-email'])
            ->assertFailed();
    }
}
