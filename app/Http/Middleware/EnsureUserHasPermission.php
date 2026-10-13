<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        if ($user->status === 'suspended') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account is suspended.'], 403);
            }

            abort(403, 'Your account has been suspended by an administrator.');
        }

        foreach ($permissions as $permission) {
            if (! $user->hasPermission($permission)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => "Forbidden: Missing required permission [{$permission}].",
                    ], 403);
                }

                abort(403, "Access denied: Missing required permission [{$permission}].");
            }
        }

        return $next($request);
    }
}
