<?php

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('unified dashboard sidebar renders profile block at the top and home and overview links', function () {
    $buyer = User::factory()->create([
        'name' => 'Abebe Bikila',
        'email' => 'abebe@example.com',
        'role' => 'buyer',
    ]);

    $response = $this->actingAs($buyer)->get(route('buyer.dashboard'));

    $response->assertOk();
    $response->assertSee('Abebe Bikila');
    $response->assertSee('Buyer');
    $response->assertSee('Home');
    $response->assertSee('Overview');
    $response->assertSee('profile-management-modal');
    $response->assertSee('openProfileModal');
});

test('user can update profile details via profile update endpoint', function () {
    $buyer = User::factory()->create([
        'name' => 'Original Name',
        'phone' => '0911000000',
        'role' => 'buyer',
    ]);

    $response = $this->actingAs($buyer)->post(route('profile.update'), [
        'name' => 'Updated Name',
        'phone' => '0922112233',
    ]);

    $response->assertSessionHas('success');
    expect($buyer->fresh()->name)->toBe('Updated Name');
    expect($buyer->fresh()->phone)->toBe('0922112233');

    // Audit log should be recorded
    $log = AuditLog::where('user_id', $buyer->id)->where('action', 'profile.updated')->latest()->first();
    expect($log)->not->toBeNull();
});

test('user can update password and toggle 2fa security settings', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
        'two_factor_enabled' => false,
    ]);

    $response = $this->actingAs($user)->post(route('profile.security'), [
        'current_password' => 'OldPassword123!',
        'password' => 'NewStrongPassword123!',
        'password_confirmation' => 'NewStrongPassword123!',
        'two_factor_enabled' => 1,
    ]);

    $response->assertSessionHas('success');
    expect(Hash::check('NewStrongPassword123!', $user->fresh()->password))->toBeTrue();
    expect($user->fresh()->two_factor_enabled)->toBeTrue();

    // Audit log should be recorded
    $log = AuditLog::where('user_id', $user->id)->where('action', 'security.updated')->latest()->first();
    expect($log)->not->toBeNull();
});

test('user can update preferences including language and notification channels', function () {
    $user = User::factory()->create([
        'language' => 'en',
    ]);

    $response = $this->actingAs($user)->post(route('profile.preferences'), [
        'language' => 'am',
        'notification_preferences' => [
            'email' => true,
            'in_app' => true,
            'sms' => false,
        ],
    ]);

    $response->assertSessionHas('success');
    expect($user->fresh()->language)->toBe('am');
    expect($user->fresh()->notification_preferences)->toBe([
        'email' => true,
        'in_app' => true,
        'sms' => false,
    ]);
});

test('user can request account deactivation with reason and gets logged out', function () {
    $user = User::factory()->create([
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('profile.deactivate'), [
        'action_type' => 'deactivate',
        'reason' => 'Taking a break from real estate transactions.',
    ]);

    $response->assertRedirect(route('login'));
    expect(auth()->check())->toBeFalse();
    expect($user->fresh()->status)->toBe('inactive');
    expect($user->fresh()->deactivated_at)->not->toBeNull();
    expect($user->fresh()->deactivation_reason)->toBe('Taking a break from real estate transactions.');

    // Audit log
    $log = AuditLog::where('user_id', $user->id)->where('action', 'account.deactivated')->latest()->first();
    expect($log)->not->toBeNull();
});

test('user can retrieve login history from audit logs via api', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'actor_name' => $user->name,
        'action' => 'auth.login',
        'ip_address' => '127.0.0.1',
        'device' => 'Mozilla/5.0 Chrome',
        'reason' => 'User logged in successfully',
    ]);

    $response = $this->actingAs($user)->getJson(route('profile.history'));

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonFragment(['ip_address' => '127.0.0.1']);
});

test('granting permission to buyer makes item appear in sidebar and access is allowed', function () {
    $buyer = User::factory()->create([
        'role' => 'buyer',
    ]);

    // Ensure property.create permission exists and maps to user.properties.create
    $perm = Permission::firstOrCreate(
        ['key' => 'property.create'],
        [
            'label' => 'Post Property',
            'menu_label' => 'Post Property',
            'icon' => '🏠',
            'route' => 'user.properties.create',
            'group' => 'property',
            'sort_order' => 10,
        ]
    );

    // Initially, buyer does not have property.create -> accessing create returns 403
    $responseInitial = $this->actingAs($buyer)->get(route('user.properties.create'));
    $responseInitial->assertForbidden();

    // Now grant permission to buyer
    UserPermissionOverride::create([
        'user_id' => $buyer->id,
        'permission_id' => $perm->id,
        'granted' => true,
    ]);

    // Now item should appear in sidebar
    $responseGranted = $this->actingAs($buyer)->get(route('buyer.dashboard'));
    $responseGranted->assertOk();
    $responseGranted->assertSee('Post Property');

    // And URL should return 200 OK
    $responseAccess = $this->actingAs($buyer)->get(route('user.properties.create'));
    $responseAccess->assertOk();
});

test('revoking permission removes item from sidebar and URL returns 403', function () {
    $buyer = User::factory()->create([
        'role' => 'buyer',
    ]);

    $perm = Permission::firstOrCreate(
        ['key' => 'property.create'],
        [
            'label' => 'Post Property',
            'menu_label' => 'Post Property',
            'icon' => '🏠',
            'route' => 'user.properties.create',
            'group' => 'property',
            'sort_order' => 10,
        ]
    );

    // Explicitly deny permission
    UserPermissionOverride::create([
        'user_id' => $buyer->id,
        'permission_id' => $perm->id,
        'granted' => false,
    ]);

    // Item should NOT appear in Granted Access section
    $responseDashboard = $this->actingAs($buyer)->get(route('buyer.dashboard'));
    $responseDashboard->assertOk();
    $responseDashboard->assertDontSee('id="authorized-granted-sidebar-section"', false);

    // Access to URL returns 403
    $responseAccess = $this->actingAs($buyer)->get(route('user.properties.create'));
    $responseAccess->assertForbidden();
});

test('non-admin user cannot open admin dashboard', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $response = $this->actingAs($buyer)->get(route('dashboard'));
    $response->assertForbidden();
});

test('admin cannot impersonate another user and returns 403', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['role' => 'buyer']);

    $response = $this->actingAs($admin)->post(route('dashboard.users.impersonate', $target));
    $response->assertForbidden();
});

test('buyer host and agent: grant permission shows item and works, revoke removes item and returns 403, and no admin links appear', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    $host = User::factory()->create(['role' => 'host']);
    $agent = User::factory()->create(['role' => 'agent']);

    // 1. BUYER test with property.create
    $permPropertyCreate = Permission::firstOrCreate(
        ['key' => 'property.create'],
        ['label' => 'Post Property', 'menu_label' => 'Post Property', 'icon' => '🏠', 'route' => 'user.properties.create', 'group' => 'property', 'sort_order' => 1]
    );

    // Initial state: not granted
    $this->actingAs($buyer)->get(route('user.properties.create'))->assertForbidden();
    $buyerDash = $this->actingAs($buyer)->get(route('buyer.dashboard'));
    $buyerDash->assertOk();
    $buyerDash->assertDontSee(route('dashboard'));

    // Grant property.create to buyer
    UserPermissionOverride::create(['user_id' => $buyer->id, 'permission_id' => $permPropertyCreate->id, 'granted' => true]);
    $this->actingAs($buyer)->get(route('user.properties.create'))->assertOk();
    $this->actingAs($buyer)->get(route('buyer.dashboard'))->assertSee('Post Property');

    // Revoke property.create
    UserPermissionOverride::where(['user_id' => $buyer->id, 'permission_id' => $permPropertyCreate->id])->update(['granted' => false]);
    $this->actingAs($buyer)->get(route('user.properties.create'))->assertForbidden();

    // 2. HOST test with viewings.view
    $permViewing = Permission::firstOrCreate(
        ['key' => 'viewings.view'],
        ['label' => 'My Viewings', 'menu_label' => 'My Viewings', 'icon' => '👁️', 'route' => 'user.viewings', 'group' => 'viewing', 'sort_order' => 2]
    );

    // Initial state: not granted to host
    $this->actingAs($host)->get(route('user.viewings'))->assertForbidden();
    $hostDash = $this->actingAs($host)->get(route('host.dashboard'));
    $hostDash->assertOk();
    $hostDash->assertDontSee(route('dashboard'));

    // Grant viewings.view to host
    UserPermissionOverride::create(['user_id' => $host->id, 'permission_id' => $permViewing->id, 'granted' => true]);
    $this->actingAs($host)->get(route('user.viewings'))->assertOk();
    $this->actingAs($host)->get(route('host.dashboard'))->assertSee('My Viewings');

    // Revoke viewings.view
    UserPermissionOverride::where(['user_id' => $host->id, 'permission_id' => $permViewing->id])->update(['granted' => false]);
    $this->actingAs($host)->get(route('user.viewings'))->assertForbidden();

    // 3. AGENT test with bookings.view
    $permBooking = Permission::firstOrCreate(
        ['key' => 'bookings.view'],
        ['label' => 'My Bookings', 'menu_label' => 'My Bookings', 'icon' => '🧳', 'route' => 'user.bookings', 'group' => 'booking', 'sort_order' => 3]
    );

    // Initial state: agent does not have user.bookings permission
    $this->actingAs($agent)->get(route('user.bookings'))->assertForbidden();
    $agentDash = $this->actingAs($agent)->get(route('agent.portal'));
    $agentDash->assertOk();
    $agentDash->assertDontSee(route('dashboard'));

    // Grant bookings.view to agent
    UserPermissionOverride::create(['user_id' => $agent->id, 'permission_id' => $permBooking->id, 'granted' => true]);
    $this->actingAs($agent)->get(route('user.bookings'))->assertRedirect($agent->dashboard_url.'#bookings');

    // Revoke bookings.view
    UserPermissionOverride::where(['user_id' => $agent->id, 'permission_id' => $permBooking->id])->update(['granted' => false]);
    $this->actingAs($agent)->get(route('user.bookings'))->assertForbidden();
});

test('no Granted Access text exists and no sidebar label is duplicated for any role', function (string $role) {
    $user = User::factory()->create(['role' => $role]);
    if ($role === 'agent') {
        $user->ensureAgentProfile();
    }

    $response = $this->actingAs($user)->get($user->dashboard_url);
    $response->assertOk();

    // 1. Assert no "Granted Access" text or block
    $response->assertDontSee('Granted Access');
    $response->assertDontSee('id="authorized-granted-sidebar-section"', false);

    // 2. Extract sidebar content
    $content = $response->getContent();
    expect($content)->toMatch('/<aside[^>]*class="[^"]*dashboard-sidebar[^"]*"[^>]*>(.*?)<\/aside>/s');

    preg_match('/<aside[^>]*class="[^"]*dashboard-sidebar[^"]*"[^>]*>(.*?)<\/aside>/s', $content, $sidebarMatch);
    $sidebarHtml = $sidebarMatch[1] ?? '';

    // Extract all navigation link texts from the sidebar nav links
    preg_match_all('/<a[^>]*>(.*?)<\/a>/s', $sidebarHtml, $linkMatches);
    $labels = [];

    foreach ($linkMatches[1] as $inner) {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($inner)));
        // Remove trailing numbers or badges
        $cleanLabel = trim(preg_replace('/(\s*\d+\s*(overdue|active)?|\s*&rarr;.*|\s*→.*)$/i', '', $text));
        // Remove leading emojis
        $cleanLabel = trim(preg_replace('/^[\x{1F300}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\s]+/u', '', $cleanLabel));
        if (! empty($cleanLabel) && ! in_array($cleanLabel, ['Sign Out', 'GTM Real Estate', 'Workspace', 'Compliance Desk', 'Developer Portal'])) {
            $labels[] = $cleanLabel;
        }
    }

    // Overview must appear exactly once
    $overviewCount = count(array_filter($labels, fn ($l) => strcasecmp($l, 'Overview') === 0));
    expect($overviewCount)->toBe(1, "Role [{$role}] has Overview count of {$overviewCount}, expected exactly 1.");

    // No label may appear twice
    $counts = array_count_values(array_map('strtolower', $labels));
    $duplicates = array_filter($counts, fn ($count) => $count > 1);
    expect($duplicates)->toBeEmpty("Role [{$role}] has duplicate sidebar labels: ".json_encode($duplicates));
})->with([
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
]);

test('permission-granted features open inside the unified dashboard shell and redirect old urls', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    // Grant leases, maintenance, and bookings
    $permLease = Permission::firstOrCreate(['key' => 'leases.view'], ['label' => 'My Leases & Rent', 'menu_label' => 'My Leases & Rent', 'icon' => '🔑', 'route' => 'user.leases', 'group' => 'lease']);
    $permMaint = Permission::firstOrCreate(['key' => 'maintenance.view'], ['label' => 'Maintenance Requests', 'menu_label' => 'Maintenance Requests', 'icon' => '🛠️', 'route' => 'user.maintenance', 'group' => 'maintenance']);
    $permBook = Permission::firstOrCreate(['key' => 'bookings.view'], ['label' => 'My Bookings', 'menu_label' => 'My Bookings', 'icon' => '🧳', 'route' => 'user.bookings', 'group' => 'booking']);

    UserPermissionOverride::create(['user_id' => $buyer->id, 'permission_id' => $permLease->id, 'granted' => true]);
    UserPermissionOverride::create(['user_id' => $buyer->id, 'permission_id' => $permMaint->id, 'granted' => true]);
    UserPermissionOverride::create(['user_id' => $buyer->id, 'permission_id' => $permBook->id, 'granted' => true]);

    // Test direct old URLs redirect to dashboard with hash
    $this->actingAs($buyer)->get(route('user.leases'))->assertRedirect($buyer->dashboard_url.'#leases');
    $this->actingAs($buyer)->get(route('user.maintenance'))->assertRedirect($buyer->dashboard_url.'#maintenance');
    $this->actingAs($buyer)->get(route('user.bookings'))->assertRedirect($buyer->dashboard_url.'#bookings');

    // Dashboard renders unified panels for all three features
    $dashboardResponse = $this->actingAs($buyer)->get($buyer->dashboard_url);
    $dashboardResponse->assertOk();
    $dashboardResponse->assertSee('id="leases"', false);
    $dashboardResponse->assertSee('id="maintenance"', false);
    $dashboardResponse->assertSee('id="bookings"', false);

    // Sidebar renders items under normal groups without Granted Access header
    $dashboardResponse->assertDontSee('Granted Access');
    $dashboardResponse->assertSee('Leases & Rent');
    $dashboardResponse->assertSee('Maintenance Requests');
    $dashboardResponse->assertSee('My Bookings');
});

test('revoking granted permission removes item from sidebar and URL returns 403', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $permLease = Permission::firstOrCreate(['key' => 'leases.view'], ['label' => 'Leases & Rent', 'menu_label' => 'Leases & Rent', 'icon' => '🔑', 'route' => 'user.leases', 'group' => 'lease']);
    UserPermissionOverride::create(['user_id' => $buyer->id, 'permission_id' => $permLease->id, 'granted' => false]);

    // URL returns 403
    $this->actingAs($buyer)->get(route('user.leases'))->assertForbidden();

    // Dashboard does not show Leases & Rent
    $dashboardResponse = $this->actingAs($buyer)->get($buyer->dashboard_url);
    $dashboardResponse->assertOk();
    $dashboardResponse->assertDontSee('Leases & Rent');
});

test('design token color palette complies with WCAG AA minimum contrast ratio 4.5:1', function () {
    $luminance = function ($hex) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $transform = fn ($c) => ($c <= 0.03928) ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);

        return 0.2126 * $transform($r) + 0.7152 * $transform($g) + 0.0722 * $transform($b);
    };

    $contrast = function ($c1, $c2) use ($luminance) {
        $l1 = $luminance($c1);
        $l2 = $luminance($c2);
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    };

    $tokenPairs = [
        // Light mode
        ['text' => '#0f172a', 'bg' => '#ffffff', 'label' => 'Light Text on Surface', 'min' => 4.5],
        ['text' => '#475569', 'bg' => '#ffffff', 'label' => 'Light Muted Text on Surface', 'min' => 4.5],
        ['text' => '#1d3c34', 'bg' => '#ffffff', 'label' => 'Light Primary on Surface', 'min' => 4.5],
        ['text' => '#047857', 'bg' => '#ffffff', 'label' => 'Light Success on Surface', 'min' => 4.5],
        ['text' => '#b45309', 'bg' => '#ffffff', 'label' => 'Light Warning on Surface', 'min' => 4.5],
        ['text' => '#b91c1c', 'bg' => '#ffffff', 'label' => 'Light Danger on Surface', 'min' => 4.5],
        ['text' => '#1d4ed8', 'bg' => '#ffffff', 'label' => 'Light Info on Surface', 'min' => 4.5],
        ['text' => '#064e3b', 'bg' => '#ecfdf5', 'label' => 'Light Success Badge Text on Surface', 'min' => 4.5],
        ['text' => '#78350f', 'bg' => '#fffbeb', 'label' => 'Light Warning Badge Text on Surface', 'min' => 4.5],
        ['text' => '#7f1d1d', 'bg' => '#fef2f2', 'label' => 'Light Danger Badge Text on Surface', 'min' => 4.5],
        ['text' => '#1e3a8a', 'bg' => '#eff6ff', 'label' => 'Light Info Badge Text on Surface', 'min' => 4.5],

        // Dark mode (against #111827 surface)
        ['text' => '#f8fafc', 'bg' => '#111827', 'label' => 'Dark Text on Surface', 'min' => 4.5],
        ['text' => '#94a3b8', 'bg' => '#111827', 'label' => 'Dark Muted Text on Surface', 'min' => 4.5],
        ['text' => '#34d399', 'bg' => '#111827', 'label' => 'Dark Primary on Surface', 'min' => 4.5],
        ['text' => '#fbbf24', 'bg' => '#111827', 'label' => 'Dark Warning on Surface', 'min' => 4.5],
        ['text' => '#f87171', 'bg' => '#111827', 'label' => 'Dark Danger on Surface', 'min' => 4.5],
        ['text' => '#60a5fa', 'bg' => '#111827', 'label' => 'Dark Info on Surface', 'min' => 4.5],
        ['text' => '#6ee7b7', 'bg' => '#052e22', 'label' => 'Dark Success Badge Text on Surface', 'min' => 4.5],
        ['text' => '#fcd34d', 'bg' => '#381e05', 'label' => 'Dark Warning Badge Text on Surface', 'min' => 4.5],
        ['text' => '#fca5a5', 'bg' => '#3b0d0c', 'label' => 'Dark Danger Badge Text on Surface', 'min' => 4.5],
        ['text' => '#93c5fd', 'bg' => '#0c2340', 'label' => 'Dark Info Badge Text on Surface', 'min' => 4.5],

        // Banner text-on-color
        ['text' => '#ffffff', 'bg' => '#1d3c34', 'label' => 'Banner White Text on Primary Dark', 'min' => 4.5],
        ['text' => '#dfeee4', 'bg' => '#1d3c34', 'label' => 'Banner Mint Subtitle on Primary Dark', 'min' => 4.5],
    ];

    foreach ($tokenPairs as $pair) {
        $ratio = $contrast($pair['text'], $pair['bg']);
        expect($ratio)->toBeGreaterThanOrEqual(
            $pair['min'],
            "Contrast ratio for [{$pair['label']}] is {$ratio}:1, which is below WCAG AA {$pair['min']}:1 requirement."
        );
    }
});
