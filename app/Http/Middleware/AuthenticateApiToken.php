<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Handle an incoming request authenticated via Bearer API Token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (! $bearer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated: Bearer token is missing.',
            ], 401);
        }

        $apiToken = ApiToken::findToken($bearer);

        if (! $apiToken || ! $apiToken->user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired bearer token.',
            ], 401);
        }

        $apiToken->update(['last_used_at' => now()]);
        Auth::setUser($apiToken->user);
        $request->setUserResolver(fn () => $apiToken->user);

        return $next($request);
    }
}
