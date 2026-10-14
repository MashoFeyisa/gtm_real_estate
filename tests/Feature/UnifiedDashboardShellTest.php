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
    $this->actingAs($agent)->get(route('user.bookings'))->assertOk();

    // Revoke bookings.view
    UserPermissionOverride::where(['user_id' => $agent->id, 'permission_id' => $permBooking->id])->update(['granted' => false]);
    $this->actingAs($agent)->get(route('user.bookings'))->assertForbidden();
});
