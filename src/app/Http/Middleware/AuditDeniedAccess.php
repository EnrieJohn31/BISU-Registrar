<?php

namespace App\Http\Middleware;

use App\Enums\AuditAction;
use App\Enums\AuditOutcome;
use App\Models\User;
use App\Support\Audit\AuditRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditDeniedAccess
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Record refused requests on protected route groups (administration now,
     * financial actions later). Applied per route group to keep the log useful.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $user = $request->user();

        if ($response->getStatusCode() === Response::HTTP_FORBIDDEN && $user instanceof User) {
            $this->audit->record(
                AuditAction::AuthDenied,
                context: [
                    'route' => $request->route()?->getName(),
                    'method' => $request->method(),
                    'path' => $request->path(),
                ],
                outcome: AuditOutcome::Denied,
                actor: $user,
            );
        }

        return $response;
    }
}
