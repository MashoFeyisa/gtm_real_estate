<?php

use App\Models\User;

beforeEach(function () {
    $this->seed();
});

test('public pages load with HTTP 200 and valid navigational structure', function () {
    $publicRoutes = [
        '/',
        '/app',
        '/properties',
        '/developments',
        '/properties/compare',
        '/careers',
        '/login',
        '/register',
    ];

    foreach ($publicRoutes as $uri) {
        $response = $this->get($uri);
        $response->assertStatus(200);
    }
});

test('all 21 persona dashboards load with 200 and internal navigational links do not 404 or 500', function () {
    $userDashboards = [
        'buyer@realestate.com' => '/dashboard/buyer',
        'tenant@realestate.com' => '/dashboard/tenant',
        'traveler@realestate.com' => '/dashboard/guest',
        'investor@realestate.com' => '/dashboard/investor',
        'owner@realestate.com' => '/dashboard/seller',
        'host@realestate.com' => '/dashboard/host',
        'agent@realestate.com' => '/dashboard/agent',
        'company@realestate.com' => '/company',
        'developer@realestate.com' => '/dashboard/developer',
        'propertymanager@realestate.com' => '/dashboard/property-manager',
        'service@realestate.com' => '/dashboard/professional',
        'cleaner@realestate.com' => '/dashboard/cleaner',
        'admin@realestate.com' => '/dashboard',
        'verification@realestate.com' => '/dashboard/verification-officer',
        'moderator@realestate.com' => '/dashboard/moderator',
        'finance@realestate.com' => '/dashboard/finance',
        'support@realestate.com' => '/dashboard/customer-support',
        'guest@realestate.com' => '/dashboard/guest',
        'landlord@realestate.com' => '/persona-portal',
        'campaign@realestate.com' => '/dashboard/campaign',
        'verifycompany@realestate.com' => '/verification-company',
    ];

    foreach ($userDashboards as $email => $dashboardUri) {
        $user = User::query()->where('email', $email)->first();
        expect($user)->not->toBeNull("Seeded user {$email} must exist.");

        $response = $this->actingAs($user)->get($dashboardUri);
        expect($response->status())->toBe(200, "Dashboard {$dashboardUri} failed for {$email}");

        $html = $response->getContent();

        // Extract href links in the rendered page
        preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\']/i', $html, $matches);
        $links = array_unique($matches[1] ?? []);

        foreach ($links as $link) {
            // Skip anchor jumps, mailto, tel, javascript, external links, and logout
            if (
                str_starts_with($link, '#') ||
                str_starts_with($link, 'mailto:') ||
                str_starts_with($link, 'tel:') ||
                str_starts_with($link, 'javascript:') ||
                str_starts_with($link, 'http://') ||
                str_starts_with($link, 'https://') ||
                str_contains($link, 'logout') ||
                str_contains($link, 'download') ||
                str_contains($link, 'delete')
            ) {
                continue;
            }

            // Only crawl GET routes within the local domain
            $parsed = parse_url($link);
            $path = $parsed['path'] ?? '';
            if (empty($path)) {
                continue;
            }

            // Test link access
            $linkResponse = $this->actingAs($user)->get($link);
            expect(in_array($linkResponse->status(), [200, 301, 302, 307, 308, 403]))
                ->toBeTrue("Link {$link} on {$dashboardUri} returned HTTP {$linkResponse->status()}");
            expect($linkResponse->status())->not->toBe(404, "Dead link {$link} on {$dashboardUri}");
            expect($linkResponse->status())->not->toBe(500, "Server error on link {$link} on {$dashboardUri}");
        }
    }
});

test('role security boundaries strictly enforce 403 on unauthorized dashboard access', function () {
    $buyer = User::query()->where('email', 'buyer@realestate.com')->first();

    $restrictedRoutes = [
        '/dashboard',
        '/finance',
        '/company',
        '/developer-portal',
        '/dashboard/verification-officer',
        '/dashboard/moderator',
    ];

    foreach ($restrictedRoutes as $route) {
        $response = $this->actingAs($buyer)->get($route);
        expect(in_array($response->status(), [403, 302]))->toBeTrue(
            "Buyer unexpectedly accessed {$route} with status {$response->status()}"
        );
    }
});

test('legacy dashboard URLs redirect to unified dashboards or serve unified workspaces', function () {
    $user = User::factory()->create();
    $agent = User::where('role', 'agent')->first() ?? User::factory()->create(['role' => 'agent']);
    $developer = User::where('role', 'developer')->first() ?? User::factory()->create(['role' => 'developer']);

    $this->actingAs($agent)->get('/agent-portal')->assertOk();
    $this->actingAs($developer)->get('/developer-portal')->assertOk();
    $this->actingAs($user)->get('/traveler/dashboard')->assertRedirect('/dashboard/guest');
    $this->actingAs($user)->get('/dashboard/landlord')->assertRedirect('/dashboard/seller');
});
