<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class RecordSuccessfulLogin
{
    /**
     * Keep the last sign-in time for the semester access review. Written
     * without touching updated_at.
     */
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        User::query()->whereKey($event->user->getAuthIdentifier())->toBase()->update(['last_login_at' => now()]);
    }
}
