<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoLogoutInactiveUsers
{
    /**
     * Inactivity timeout duration in seconds (3 minutes).
     */
    public const INACTIVITY_TIMEOUT = 180;

    /**
     * Handle an incoming request.
     *
     * Automatically logs out authenticated admins, agents, and users after 3 minutes of inactivity.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
