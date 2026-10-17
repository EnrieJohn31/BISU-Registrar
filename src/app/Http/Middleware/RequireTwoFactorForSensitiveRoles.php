<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactorForSensitiveRoles
{
    /**
     * Routes needed to enroll two-factor from the security settings page.
     */
    private const ALLOWED_ROUTES = [
        'security.edit',
        'user-password.update',
        'password.confirm',
        'password.confirm.store',
        'password.confirmation',
        'two-factor.*',
        'passkey.*',
        'logout',
    ];

    /**
     * Roles that handle money, approvals, releases, supervision or administration
     * need confirmed two-factor authentication. A passkey alone does not protect
     * password sign-in, so it does not satisfy this rule.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User
            || ! $user->requiresTwoFactor()
            || $user->hasEnabledTwoFactorAuthentication()
            || $request->routeIs(...self::ALLOWED_ROUTES)
            || $request->is('_inertia/*')) {
            return $next($request);
        }

        Inertia::flash('toast', [
            'type' => 'warning',
            'message' => __('Your role requires two-factor authentication. Set it up to continue.'),
        ]);

        return redirect()->route('security.edit');
    }
}
