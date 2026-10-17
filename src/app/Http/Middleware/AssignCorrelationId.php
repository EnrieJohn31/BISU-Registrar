<?php

namespace App\Http\Middleware;

use App\Support\Audit\CorrelationId;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssignCorrelationId
{
    /**
     * Give every request a fresh correlation id for its audit events.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        CorrelationId::start();

        return $next($request);
    }
}
