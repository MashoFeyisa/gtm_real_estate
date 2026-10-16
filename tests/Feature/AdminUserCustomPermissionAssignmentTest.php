<?php

use App\Models\Permission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can create user with grant everything (all permissions) mode', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('dashboard.users.store'), [
        'name' => 'Super Buyer',
        'email' => 'superbuyer@example.com',
        'password' => 'Pass!Word1234',
        'role' => 'buyer',
        'permission_mode' => 'all',
    ]);

    $response->assertRedirect(route('dashboard', ['section' => 'users']));

    $createdUser = User::where('email', 'superbuyer@example.com')->first();
    expect($createdUser)->not->toBeNull()
        ->and($createdUser->role)->toBe('buyer');

    // Although their role is buyer, they hold permissions from all categories because mode was 'all'
    expect($createdUser->hasPermission('properties.create'))->toBeTrue()
        ->and($createdUser->hasPermission('properties.publish'))->toBeTrue()
        ->and($createdUser->hasPermission('finance.audit'))->toBeTrue()
        ->and($createdUser->hasPermission('projects.create'))->toBeTrue()
        ->and($createdUser->hasPermission('stays.reserve'))->toBeTrue();

    // Verify user_permission_overrides contains all permissions with granted=true
    $allPermCount = Permission::count();
    expect($createdUser->permissionOverrides()->where('granted', true)->count())->toBe($allPermCount);
});

test('admin can create user with custom selected permissions by category', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Pick developer and property owner permissions
    $customPermIds = Permission::whereIn('key', [
        'properties.create',
        'properties.publish',
        'projects.create',
        'units.create',
    ])->pluck('id')->all();

    $response = $this->actingAs($admin)->post(route('dashboard.users.store'), [
        'name' => 'Custom Privileged Buyer',
        'email' => 'privilegedbuyer@example.com',
        'password' => 'Pass!Word1234',
        'role' => 'buyer',
        'permission_mode' => 'custom',
        'granted_permissions' => $customPermIds,
    ]);

    $response->assertRedirect(route('dashboard', ['section' => 'users']));

    $createdUser = User::where('email', 'privilegedbuyer@example.com')->first();
    expect($createdUser)->not->toBeNull();

    // Custom granted permissions are true
    expect($createdUser->hasPermission('properties.create'))->toBeTrue()
        ->and($createdUser->hasPermission('properties.publish'))->toBeTrue()
        ->and($createdUser->hasPermission('projects.create'))->toBeTrue()
        ->and($createdUser->hasPermission('units.create'))->toBeTrue();
});

test('admin can create user with default role permissions', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('dashboard.users.store'), [
        'name' => 'Standard Tenant',
        'email' => 'standardtenant@example.com',
        'password' => 'Pass!Word1234',
        'role' => 'tenant',
        'permission_mode' => 'role_default',
    ]);

    $response->assertRedirect(route('dashboard', ['section' => 'users']));

    $createdUser = User::where('email', 'standardtenant@example.com')->first();
    expect($createdUser)->not->toBeNull();

    // Role default for tenant: has tenant/lease view, but no custom overrides
    expect($createdUser->permissionOverrides()->count())->toBe(0);
});

test('admin can update user permissions via quick modal endpoint to grant everything', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $buyer = User::factory()->create(['role' => 'buyer']);

    expect($buyer->hasPermission('properties.create'))->toBeFalse();

    $response = $this->actingAs($admin)->post(route('dashboard.users.permissions.update', $buyer), [
        'permission_mode' => 'all',
        'reason' => 'Elevated to full capabilities by administrator',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $allCount = Permission::count();
    $freshBuyer = $buyer->fresh();

    expect($freshBuyer->hasPermission('properties.create'))->toBeTrue()
        ->and($freshBuyer->hasPermission('projects.create'))->toBeTrue()
        ->and($freshBuyer->permissionOverrides()->where('granted', true)->count())->toBe($allCount);
});

test('admin can reset user permissions back to role defaults', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $buyer = User::factory()->create(['role' => 'buyer']);

    // First elevate buyer
    $this->actingAs($admin)->post(route('dashboard.users.permissions.update', $buyer), [
        'permission_mode' => 'all',
    ]);
    expect($buyer->fresh()->hasPermission('properties.create'))->toBeTrue();

    // Now reset to role defaults
    $response = $this->actingAs($admin)->post(route('dashboard.users.permissions.update', $buyer), [
        'permission_mode' => 'role_default',
        'reason' => 'Reset back to standard buyer defaults',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $freshBuyer = $buyer->fresh();
    expect($freshBuyer->permissionOverrides()->count())->toBe(0)
        ->and($freshBuyer->hasPermission('properties.create'))->toBeFalse();
});

test('admin can assign custom category permissions to existing user via user update', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $tenant = User::factory()->create(['role' => 'tenant']);

    $permIds = Permission::whereIn('key', ['maintenance.create', 'properties.view'])->pluck('id')->all();

    $response = $this->actingAs($admin)->put(route('dashboard.users.update', $tenant), [
        'name' => $tenant->name,
        'email' => $tenant->email,
        'role' => 'tenant',
        'permission_mode' => 'custom',
        'granted_permissions' => $permIds,
    ]);

    $response->assertRedirect(route('dashboard', ['section' => 'users']));

    $freshTenant = $tenant->fresh();
    expect($freshTenant->hasPermission('maintenance.create'))->toBeTrue();
});

test('non-admin cannot update user permissions', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $targetUser = User::factory()->create(['role' => 'tenant']);

    $response = $this->actingAs($buyer)->post(route('dashboard.users.permissions.update', $targetUser), [
        'permission_mode' => 'all',
    ]);

    $response->assertForbidden();
});

test('sidebar does not show granted access desks when user has role default permissions', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $response = $this->actingAs($buyer)->get(route('buyer.dashboard'));

    $response->assertOk();
    $response->assertDontSee(route('seller.dashboard'));
    $response->assertDontSee(route('developer.portal'));
    $response->assertDontSee(route('finance.index'));
});

test('sidebar dynamically renders granted capability feature when admin grants permissions to user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $buyer = User::factory()->create(['role' => 'buyer']);

    // Admin grants properties.create and booking.view to buyer
    $permIds = Permission::whereIn('key', ['properties.create', 'booking.view'])->pluck('id')->all();

    $this->actingAs($admin)->post(route('dashboard.users.permissions.update', $buyer), [
        'permission_mode' => 'custom',
        'granted_permissions' => $permIds,
    ])->assertRedirect();

    $buyer->refresh();

    // Buyer visits dashboard; sidebar includes normal grouped links, no Granted Access header
    $response = $this->actingAs($buyer)->get(route('buyer.dashboard'));

    $response->assertOk();
    $response->assertDontSee('Granted Access');
    $response->assertSee('Post Property');
    $response->assertSee(route('user.properties.create'));
    $response->assertSee('My Bookings');
    $response->assertSee('#bookings');

    // And buyer can successfully access user-facing granted pages (direct bookings redirects to shell)
    $this->actingAs($buyer)->get(route('user.properties.create'))->assertOk();
    $this->actingAs($buyer)->get(route('user.bookings'))->assertRedirect(route('buyer.dashboard').'#bookings');
});

test('sidebar dynamically shows all user features when admin grants everything and hides them when reset', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $buyer = User::factory()->create(['role' => 'buyer']);

    // Admin grants everything
    $this->actingAs($admin)->post(route('dashboard.users.permissions.update', $buyer), [
        'permission_mode' => 'all',
    ])->assertRedirect();

    $buyer->refresh();

    $response = $this->actingAs($buyer)->get(route('buyer.dashboard'));
    $response->assertOk();
    $response->assertDontSee('Granted Access');
    $response->assertSee(route('user.properties.create'));
    $response->assertSee('#bookings');
    $response->assertSee('#maintenance');

    // Admin resets to role default
    $this->actingAs($admin)->post(route('dashboard.users.permissions.update', $buyer), [
        'permission_mode' => 'role_default',
    ])->assertRedirect();

    $buyer->refresh();

    $responseReset = $this->actingAs($buyer)->get(route('buyer.dashboard'));
    $responseReset->assertOk();
    $responseReset->assertDontSee('Granted Access');
    $responseReset->assertDontSee(route('user.properties.create'));
    $responseReset->assertDontSee('#bookings');
});

test('developer portal renders granted access links when developer has additional permissions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $developer = User::factory()->create(['role' => 'developer']);

    // Admin grants booking.view permission to developer
    $permId = Permission::where('key', 'booking.view')->value('id');

    $this->actingAs($admin)->post(route('dashboard.users.permissions.update', $developer), [
        'permission_mode' => 'custom',
        'granted_permissions' => [$permId],
    ])->assertRedirect();

    $developer->refresh();

    $response = $this->actingAs($developer)->get(route('developer.portal'));
    $response->assertOk();
    $response->assertDontSee('Granted Access');
    $response->assertSee('My Bookings');
    $response->assertSee('#bookings');
});

test('agent portal renders granted access links when agent has additional user permissions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $agentUser = User::factory()->create(['role' => 'agent']);
    $agentUser->ensureAgentProfile();

    // Admin grants booking.view permission to agent
    $permId = Permission::where('key', 'booking.view')->value('id');

    $this->actingAs($admin)->post(route('dashboard.users.permissions.update', $agentUser), [
        'permission_mode' => 'custom',
        'granted_permissions' => [$permId],
    ])->assertRedirect();

    $agentUser->refresh();

    $response = $this->actingAs($agentUser)->get(route('agent.portal'));
    $response->assertOk();
    $response->assertDontSee('Granted Access');
    $response->assertSee('My Bookings');
    $response->assertSee('#bookings');
});
