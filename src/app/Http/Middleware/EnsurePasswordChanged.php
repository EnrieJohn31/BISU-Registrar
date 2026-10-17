<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\EndsSession;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    use EndsSession;

    /**
     * Route names usable while a temporary password is still active.
     */
    private const ALLOWED_ROUTES = ['password.change.edit', 'password.change.update', 'logout'];

    /**
     * A temporary password must be replaced before anything else.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->must_change_password) {
            return $next($request);
        }

        if ($user->hasTemporaryPasswordExpired()) {
            return $this->endSession($request, __('Your temporary password has expired. Ask a system administrator for a new one.'));
        }

        if ($request->routeIs(...self::ALLOWED_ROUTES) || $request->is('_inertia/*')) {
            return $next($request);
        }

        return redirect()->route('password.change.edit');
    }
}
