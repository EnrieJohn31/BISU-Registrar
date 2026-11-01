<?php

namespace App\Support\Accounts;

use App\Models\User;
use App\Notifications\DevelopmentTwoFactorCode;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;

/**
 * Development-only (C22): emails the current authenticator code to the local
 * test inbox. The code still comes from the account's TOTP secret, so sign-in
 * and setup are unchanged; outside the local environment this is always off.
 */
class DevelopmentTwoFactorEmail
{
    public static function enabled(): bool
    {
        return app()->isLocal() && config('bisu.dev_two_factor_email') === true;
    }

    /**
     * The test inbox link shown beside the button, or null when the helper is off.
     */
    public static function inboxUrl(): ?string
    {
        return self::enabled() ? config()->string('bisu.dev_mail_inbox_url') : null;
    }

    public static function send(User $user): void
    {
        $secret = Fortify::currentEncrypter()->decrypt((string) $user->two_factor_secret);

        $user->notify(new DevelopmentTwoFactorCode(app(Google2FA::class)->getCurrentOtp($secret)));
    }
}
