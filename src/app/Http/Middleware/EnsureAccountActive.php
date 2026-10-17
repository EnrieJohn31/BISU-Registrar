<?php

namespace App\Http\Middleware;

use App\Enums\AuditAction;
use App\Enums\AuditOutcome;
use App\Http\Middleware\Concerns\EndsSession;
use App\Models\User;
use App\Support\Audit\AuditRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    use EndsSession;

    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * End any session that belongs to a deactivated account, whichever way it
     * was opened (password, two-factor, passkey or remember-me).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->isDeactivated()) {
            $this->audit->record(
                AuditAction::AuthDenied,
                subject: $user,
                context: ['reason' => 'account deactivated', 'path' => $request->path()],
                outcome: AuditOutcome::Denied,
                actor: $user,
            );

            return $this->endSession($request, __('This account is not active. Please contact the Registrar\'s Office.'));
        }

        return $next($request);
    }
}
