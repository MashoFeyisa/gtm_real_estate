<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAgent
{
    /**
     * Ensure the authenticated user is linked to an agent account.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            $user->ensureAgentProfile();

            return $next($request);
        }

        $catalog = $user->permissionCatalog();
        $hasAgentPerm = ! empty($catalog) && $user->hasAnyPermission(['crm.view', 'crm.manage', 'leads.manage']);

        if ($hasAgentPerm) {
            $user->ensureAgentProfile();

            return $next($request);
        }

        if (! $user->agentProfile || ! $user->agentProfile->is_active) {
            abort(403, 'Your agent account is currently deactivated. Please contact an administrator.');
        }

        return $next($request);
    }
}
