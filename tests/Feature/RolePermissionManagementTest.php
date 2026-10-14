<?php

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('persona permission catalog seeds defaults for every role', function () {
    $this->seed(RolePermissionSeeder::class);

    expect(Permission::query()->count())->toBeGreaterThan(80);

    $adminHasRolesGrant = RolePermission::query()
        ->where('role', 'admin')
        ->whereHas('permission', fn ($query) => $query->where('key', 'platform.roles_manage'))
        ->exists();

    expect($adminHasRolesGrant)->toBeTrue();

    $clientGrantKeys = RolePermission::query()
        ->where('role', 'client')
        ->with('permission')
        ->get()
        ->pluck('permission.key');

    expect($clientGrantKeys)->toContain('offers.create', 'maintenance.create', 'stays.reserve');
});

test('admins can update a persona role permission checklist', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create(['role' => 'admin']);
    $moderatorPermissions = Permission::query()->where('group', 'moderator')->take(2)->get();

    $this->actingAs($admin)->post(route('dashboard.roles.permissions.update'), [
        'persona' => 'moderator',
        'permission_ids' => $moderatorPermissions->pluck('id')->all(),
    ])->assertRedirect();

    expect(RolePermission::query()->where('role', 'moderator')->pluck('permission_id')->sort()->values()->all())
        ->toBe($moderatorPermissions->pluck('id')->sort()->values()->all());

    $this->actingAs($admin)->post(route('dashboard.roles.permissions.update'), [
        'persona' => 'moderator',
        'permission_ids' => [$moderatorPermissions->first()->id],
    ])->assertRedirect();

    expect(RolePermission::query()->where('role', 'moderator')->count())->toBe(1);
});

test('only admins can update persona role permissions', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create(['role' => 'buyer']);

    $this->actingAs($user)->post(route('dashboard.roles.permissions.update'), [
        'persona' => 'moderator',
        'permission_ids' => [],
    ])->assertForbidden();
});

test('permission checks honour role defaults and per-user overrides', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create(['role' => 'buyer']);

    expect($user->hasPermission('offers.create'))->toBeTrue();

    UserPermissionOverride::create([
        'user_id' => $user->id,
        'permission_id' => Permission::query()->where('key', 'offers.create')->value('id'),
        'granted' => false,
    ]);

    expect($user->fresh()->hasPermission('offers.create'))->toBeFalse();

    UserPermissionOverride::create([
        'user_id' => $user->id,
        'permission_id' => Permission::query()->where('key', 'platform.roles_manage')->value('id'),
        'granted' => true,
    ]);

    expect($user->fresh()->hasPermission('platform.roles_manage'))->toBeTrue();
});

test('permissions outside the seeded catalog are not restricted', function () {
    $user = User::factory()->create(['role' => 'buyer']);

    expect($user->hasPermission('not.a.real.permission'))->toBeTrue();
});

test('persona roles land on their dedicated dashboard with their capabilities', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create(['role' => 'moderator']);

    expect($user->dashboard_url)->toBe(route('moderator.dashboard'));

    $response = $this->actingAs($user)->get(route('moderator.dashboard'));

    $response->assertOk();
    $response->assertSee('Content Moderation');
});

test('established roles keep their existing dashboards', function () {
    $this->seed(RolePermissionSeeder::class);

    expect(User::factory()->create(['role' => 'admin'])->dashboard_url)->toBe(route('dashboard'));
    expect(User::factory()->create(['role' => 'buyer'])->dashboard_url)->toBe(route('buyer.dashboard'));
    expect(User::factory()->create(['role' => 'agent'])->dashboard_url)->toBe(route('agent.portal'));
    expect(User::factory()->create(['role' => 'company'])->dashboard_url)->toBe(route('company.dashboard'));
});

test('admins can assign persona roles through user management', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create(['role' => 'user', 'email' => 'samuel@example.com']);

    $this->actingAs($admin)->put(route('dashboard.users.update', $member), [
        'name' => $member->name,
        'email' => $member->email,
        'role' => 'developer',
        'assigned_property_id' => '',
    ])->assertRedirect(route('dashboard', ['section' => 'users']));

    expect($member->fresh()->role)->toBe('developer');
});

test('admins can set per-user permission overrides through user management', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create(['role' => 'admin']);
    $buyer = User::factory()->create(['role' => 'buyer', 'email' => 'buyer@example.com']);

    $offersPermissionId = Permission::query()->where('key', 'offers.create')->value('id');
    $rolesPermissionId = Permission::query()->where('key', 'platform.roles_manage')->value('id');

    $this->actingAs($admin)->put(route('dashboard.users.update', $buyer), [
        'name' => $buyer->name,
        'email' => $buyer->email,
        'role' => 'buyer',
        'assigned_property_id' => '',
        'permission_overrides' => [
            (string) $offersPermissionId => 'deny',
            (string) $rolesPermissionId => 'allow',
        ],
    ])->assertRedirect(route('dashboard', ['section' => 'users']));

    $freshBuyer = $buyer->fresh();

    expect($freshBuyer->hasPermission('offers.create'))->toBeFalse()
        ->and($freshBuyer->hasPermission('platform.roles_manage'))->toBeTrue();
});

test('user management rejects roles outside the persona catalog', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('dashboard.users.store'), [
        'name' => 'Rogue Agent',
        'email' => 'rogue@example.com',
        'password' => 'Sup3r!Secret9',
        'role' => 'superuser',
    ])->assertSessionHasErrors('role');
});

test('admins and users with administrator role can save permissions for a user without 403', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create(['role' => 'administrator']);
    $buyer = User::factory()->create(['role' => 'buyer']);

    $perm = Permission::query()->first();

    $response = $this->actingAs($admin)
        ->postJson(route('dashboard.users.permissions.update', $buyer), [
            'permission_mode' => 'custom',
            'granted_permission_ids' => [$perm->id],
            'reason' => 'Testing save user permissions',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'section' => 'users',
        ]);

    expect($buyer->fresh()->hasPermission($perm->key))->toBeTrue();
});

test('delegated user with platform.roles_manage or platform.users_manage can save permissions without 403', function () {
    $this->seed(RolePermissionSeeder::class);

    $delegatedStaff = User::factory()->create(['role' => 'staff']);
    $rolesManagePermId = Permission::query()->where('key', 'platform.roles_manage')->value('id');

    UserPermissionOverride::create([
        'user_id' => $delegatedStaff->id,
        'permission_id' => $rolesManagePermId,
        'granted' => true,
    ]);

    $buyer = User::factory()->create(['role' => 'buyer']);
    $perm = Permission::query()->where('key', 'stays.reserve')->first();

    $response = $this->actingAs($delegatedStaff)
        ->postJson(route('dashboard.users.permissions.update', $buyer), [
            'permission_mode' => 'custom',
            'granted_permission_ids' => [$perm->id],
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'section' => 'users',
        ]);
});

test('saving user permissions in role_default mode resets overrides without 403', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create(['role' => 'admin']);
    $buyer = User::factory()->create(['role' => 'buyer']);

    $response = $this->actingAs($admin)
        ->postJson(route('dashboard.users.permissions.update', $buyer), [
            'permission_mode' => 'role_default',
            'reason' => 'Reset to role default',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'section' => 'users',
        ]);
});
