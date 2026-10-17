<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\EndsSession;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StaffIdleTimeout
{
    use EndsSession;

    public const SESSION_KEY = 'bisu.last_activity_at';

    /**
     * Sign staff out after a period of inactivity, because office PCs are
     * shared. Staff sessions are also never restored from a remember-me cookie.
     * Client accounts keep the normal session lifetime.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isStaff()) {
            return $next($request);
        }

        if (Auth::guard('web')->viaRemember()) {
            return $this->endSession($request, __('For security, staff accounts must sign in again.'));
        }

        $minutes = config('bisu.staff_idle_minutes');
        $minutes = is_numeric($minutes) ? (int) $minutes : 30;
        $lastActivity = $request->session()->get(self::SESSION_KEY);

        if (is_int($lastActivity) && now()->getTimestamp() - $lastActivity >= $minutes * 60) {
            return $this->endSession($request, __('You were signed out after :minutes minutes of inactivity.', ['minutes' => $minutes]));
        }

        $request->session()->put(self::SESSION_KEY, now()->getTimestamp());

        return $next($request);
    }
}
