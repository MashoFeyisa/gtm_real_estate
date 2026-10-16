<?php

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;

/**
 * Authorization coverage tripwire.
 *
 * Lists every registered route that carries neither an authentication guard
 * (session `auth`, `auth:sanctum`, custom Authenticate middleware) nor an
 * authorization check (`permission:`, `agent`, `provider.signature`, `signed`,
 * `verified`, `can:`), and fails when the set changes. A new route that forgets
 * its guard therefore cannot ship silently - it must either be protected or be
 * added to the reviewed allowlist below with a reason.
 */
test('every route is authenticated, permission-guarded, or on the reviewed public allowlist', function () {
    $unprotected = collect(Route::getRoutes()->getRoutes())
        ->reject(fn (RoutingRoute $route) => routeIsGuarded($route))
        ->map(fn (RoutingRoute $route) => normalizeRouteKey($route))
        ->unique()
        ->sort()
        ->values();

    $allowlist = collect(array_keys(publicRouteAllowlist()))->sort()->values();

    $newlyExposed = $unprotected->diff($allowlist)->values();
    $staleEntries = $allowlist->diff($unprotected)->values();

    expect($newlyExposed)->toBeEmpty(
        "Route(s) without auth or permission middleware. Add a guard, or document the route in the allowlist:\n"
        .$newlyExposed->implode("\n")
    );

    expect($staleEntries)->toBeEmpty(
        "Allowlist entries that no longer match an unprotected route (remove them):\n"
        .$staleEntries->implode("\n")
    );
});

/**
 * A route is guarded when any resolved middleware is an authentication or
 * authorization middleware. Middleware groups (`web`, `api`) are expanded so
 * `auth` and `permission:` inherited from a group are seen too.
 */
function routeIsGuarded(RoutingRoute $route): bool
{
    foreach (app('router')->gatherRouteMiddleware($route) as $middleware) {
        if (! is_string($middleware)) {
            continue;
        }

        if (preg_match('/(auth|permission:|agent|provider\.signature|signed|verified|can:|password\.confirm)/i', $middleware)) {
            return true;
        }
    }

    return false;
}

/**
 * Stable `METHOD /uri` key (HEAD is dropped, methods sorted).
 */
function normalizeRouteKey(RoutingRoute $route): string
{
    $methods = collect($route->methods())
        ->reject(fn (string $method) => $method === 'HEAD')
        ->sort()
        ->values();

    return $methods->implode('|').' /'.ltrim($route->uri(), '/');
}

/**
 * Every route deliberately reachable without a session or permission check,
 * with the reason it is safe. Adding to this list is a security decision.
 *
 * @return array<string, string>
 */
function publicRouteAllowlist(): array
{
    return [
        // Public marketing / catalogue pages: browsing needs no account.
        'GET /' => 'Home page',
        'GET /about' => 'Marketing page',
        'GET /agents' => 'Public agent directory',
        'GET /agents/{agent}' => 'Public agent profile',
        'GET /app' => 'Marketing page',
        'GET /article-details' => 'Public article',
        'GET /blogs' => 'Public blog index',
        'GET /buy' => 'Public listings',
        'GET /careers' => 'Public careers page',
        'GET /commercial' => 'Public listings',
        'GET /contact' => 'Public contact page',
        'GET /developments' => 'Public developments index',
        'GET /developments/{slug}' => 'Public development detail',
        'GET /experiences' => 'Public experiences index',
        'GET /experiences/{experience}' => 'Public experience detail',
        'GET /job' => 'Public job page',
        'GET /job-details' => 'Public job detail',
        'GET /jobs' => 'Public job index',
        'GET /land' => 'Public listings',
        'GET /leadership' => 'Marketing page',
        'GET /news' => 'Public news index',
        'GET /page/{slug}' => 'Public CMS page',
        'GET /privacy-policy' => 'Legal page',
        'GET /project-details' => 'Public project detail',
        'GET /projects' => 'Public projects index',
        'GET /properties' => 'Public property index',
        'GET /properties/apartments' => 'Public listing category',
        'GET /properties/compare' => 'Public compare matrix',
        'GET /properties/condominiums' => 'Public listing category',
        'GET /properties/guest-houses' => 'Public listing category',
        'GET /properties/homes' => 'Public listing category',
        'GET /properties/hotels' => 'Public listing category',
        'GET /properties/map' => 'Public map search',
        'GET /properties/villas' => 'Public listing category',
        'GET /properties/{slug}' => 'Public property detail',
        'GET /rent' => 'Public listings',
        'GET /search-results' => 'Public search results',
        'GET /services' => 'Public services page',
        'GET /stays' => 'Public short-stay index',
        'GET /stays/{property}/calendar.ics' => 'Public iCal availability feed',
        'GET /terms' => 'Legal page',

        // Guest/auth entry points (login, registration, password reset).
        'GET /forgot-password' => 'Password reset request form',
        'GET /login' => 'Login form',
        'GET /register' => 'Registration form',
        'GET /reset-password/{token}' => 'Password reset form',
        'POST /forgot-password' => 'Password reset request',
        'POST /login' => 'Login submission',
        'POST /logout' => 'Logout is idempotent; harmless for a guest',
        'POST /register' => 'Registration submission',
        'POST /reset-password' => 'Password reset submission',

        // Public JSON APIs (read-only catalogue + token endpoints).
        'GET /api/geo/subcities/boundaries' => 'Public GIS boundaries',
        'GET /api/v1/agents' => 'Public agent API',
        'GET /api/v1/agents/{agent}' => 'Public agent API',
        'GET /api/v1/developments' => 'Public developments API',
        'GET /api/v1/developments/{project}' => 'Public developments API',
        'GET /api/v1/experiences' => 'Public experiences API',
        'GET /api/v1/properties' => 'Public properties API',
        'GET /api/v1/properties/{property}' => 'Public property API',
        'GET /api/v1/stays' => 'Public stays API',
        'GET /api/v1/syndication/listings.json' => 'Public listings syndication feed',
        'POST /api/v1/auth/login' => 'API token login',
        'POST /api/v1/auth/register' => 'API token registration',

        // Guest actions: the controller redirects/aborts when unauthenticated.
        'POST /agents/{agent}/feedback' => 'Guest feedback; controller guards',
        'POST /api/v1/ai/valuation' => 'Public valuation estimate',
        'POST /api/v1/mortgage/referral' => 'Public mortgage referral',
        'POST /campaigns/{campaign}/leads' => 'Public campaign lead capture',
        'POST /careers/apply' => 'Public job application',
        'POST /contact' => 'Public contact form',
        'POST /developments/{project}/units/{unit}/reserve' => 'Guest action; controller guards',
        'POST /experiences/{experience}/book' => 'Guest action; controller guards',
        'POST /newsletter/subscribe' => 'Public newsletter signup',
        'POST /payments' => 'Guest action; controller redirects to signup',
        'POST /properties/compare/clear' => 'Session-scoped comparison basket',
        'POST /properties/{property}/compare' => 'Session-scoped comparison basket',
        'POST /properties/{property}/favorite' => 'Guest action; controller Auth::check',
        'POST /properties/{property}/like' => 'Guest action; controller Auth::check',
        'POST /properties/{property}/offers' => 'Guest action; controller guards',
        'POST /properties/{property}/orders' => 'Guest action; controller guards',
        'POST /properties/{property}/report' => 'Guest action; controller guards',
        'POST /properties/{property}/stays/book' => 'Guest action; controller guards',
        'POST /properties/{property}/stays/calculate' => 'Public price estimate',
        'POST /properties/{property}/viewings' => 'Guest action; controller guards',
        'POST /session/keep-alive' => 'Session heartbeat; no privilege',

        // Partner webhooks: authenticated by their own shared secret inside
        // the controller, not by a session (tracked in the security backlog).
        'POST /api/v1/webhooks/smart-lock' => 'Partner webhook',
        'POST /api/v1/webhooks/utility-bills' => 'Partner webhook',

        // Media: access level + signed-URL checks inside the controller.
        'GET /media/avatars/{targetUser}' => 'Avatar proxy; controller guards',
        'GET /media/documents/{document}' => 'Document proxy; controller guards',
        'GET /media/properties/{image}' => 'Property image proxy; public listings only',

        // Locale, framework and developer tooling.
        'GET /locale/{lang}' => 'Locale switch',
        'GET /storage/{path}' => 'Local disk serving',
        'GET /up' => 'Health check',
        'PUT /storage/{path}' => 'Local disk upload (dev tooling)',
    ];
}
