<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('all top-level sidebar items across all roles have identical left offset, fixed icon box, and shared component markup', function (string $role) {
    $user = User::factory()->create([
        'role' => $role,
        'name' => 'Test '.ucfirst($role),
        'email' => "test_{$role}@example.com",
    ]);

    $url = $user->dashboard_url;
    $response = $this->actingAs($user)->get($url);
    $response->assertOk();

    $content = $response->getContent();

    // Verify sidebar exists
    expect($content)->toMatch('/<aside[^>]*class="[^"]*dashboard-sidebar[^"]*"[^>]*>(.*?)<\/aside>/s');

    preg_match('/<aside[^>]*class="[^"]*dashboard-sidebar[^"]*"[^>]*>(.*?)<\/aside>/s', $content, $sidebarMatch);
    $sidebarHtml = $sidebarMatch[1] ?? '';

    // Verify Home and Overview are present
    expect($sidebarHtml)->toContain('Home');
    expect($sidebarHtml)->toContain('Overview');

    // Extract all navigation link elements inside the sidebar navigation
    preg_match('/<nav[^>]*>(.*?)<\/nav>/s', $sidebarHtml, $navMatch);
    $navHtml = $navMatch[1] ?? '';
    expect($navHtml)->not->toBeEmpty("Role [{$role}] should render a <nav> inside sidebar.");

    preg_match_all('/<a\s+[^>]*class="([^"]*token-nav-(?:link|item)[^"]*)"[^>]*>(.*?)<\/a>/s', $navHtml, $linkMatches, PREG_SET_ORDER);
    expect(count($linkMatches))->toBeGreaterThanOrEqual(2, "Role [{$role}] must render navigation links.");

    foreach ($linkMatches as $link) {
        $classes = explode(' ', $link[1]);
        $innerHtml = $link[2];

        // Each sidebar item is either a top-level item or an indented sub-menu item
        $isTopLevel = in_array('token-nav-top-item', $classes, true);
        $isSubItem = in_array('token-nav-sub-item', $classes, true);

        expect($isTopLevel || $isSubItem)->toBeTrue(
            "Every sidebar item in role [{$role}] must have either token-nav-top-item or token-nav-sub-item. Classes: ".$link[1]
        );

        // Verify the uniform structure: .token-nav-lead wrapping .token-nav-icon and .token-nav-label
        expect($innerHtml)->toContain('token-nav-lead');
        expect($innerHtml)->toContain('token-nav-icon');
        expect($innerHtml)->toContain('token-nav-label');

        // Verify no ad-hoc rogue margin or padding override sitting directly on the link
        expect($link[1])->not->toContain('!justify-start');
        expect($link[1])->not->toContain('!text-xs');
    }

    // Verify "Home" and "Overview" specifically share the exact same top-level classes
    preg_match('/<a\s+[^>]*class="([^"]*token-nav-(?:link|item)[^"]*)"[^>]*>\s*<span class="token-nav-lead">\s*<span class="token-nav-icon"[^>]*>🏠<\/span>\s*<span class="token-nav-label">\s*Home\s*<\/span>/s', $navHtml, $homeMatch);
    expect($homeMatch)->not->toBeEmpty("Role [{$role}] Home item must use the shared component markup.");
    expect($homeMatch[1])->toContain('token-nav-top-item');

    preg_match('/<a\s+[^>]*class="([^"]*token-nav-(?:link|item)[^"]*)"[^>]*>\s*<span class="token-nav-lead">\s*<span class="token-nav-icon"[^>]*>📊<\/span>\s*<span class="token-nav-label">\s*Overview\s*<\/span>/s', $navHtml, $overviewMatch);
    expect($overviewMatch)->not->toBeEmpty("Role [{$role}] Overview item must use the shared component markup.");
    expect($overviewMatch[1])->toContain('token-nav-top-item');
})->with([
    'admin',
    'buyer',
    'tenant',
    'seller',
    'host',
    'agent',
    'company',
    'developer',
    'property_manager',
    'cleaner',
    'moderator',
    'verification_officer',
    'campaign_officer',
    'finance_officer',
    'customer_support',
    'investor',
    'guest',
    'professional',
]);

test('theme styles define mathematical equality for sidebar item left offset across all viewports and modes', function () {
    $themeCss = file_get_contents(resource_path('views/layouts/partials/theme-styles.blade.php'));

    // Check padding-left: 0.75rem on top items
    expect($themeCss)->toMatch('/\.token-nav-top-item\s*\{[^}]*padding-left:\s*0\.75rem;/s');

    // Check padding-left: 2.25rem on sub items
    expect($themeCss)->toMatch('/\.token-nav-sub-item\s*\{[^}]*padding-left:\s*2\.25rem;/s');

    // Check fixed 24px (1.5rem) icon box width & min-width & max-width
    expect($themeCss)->toMatch('/\.token-nav-icon\s*\{[^}]*width:\s*1\.5rem;/s');
    expect($themeCss)->toMatch('/\.token-nav-icon\s*\{[^}]*min-width:\s*1\.5rem;/s');
    expect($themeCss)->toMatch('/\.token-nav-icon\s*\{[^}]*max-width:\s*1\.5rem;/s');

    // Check uniform gap on lead container
    expect($themeCss)->toMatch('/\.token-nav-lead\s*\{[^}]*gap:\s*0\.75rem;/s');

    // Check collapsed sidebar support
    expect($themeCss)->toContain('.dashboard-sidebar.is-collapsed');
    expect($themeCss)->toContain('width: 4.5rem !important;');
});
