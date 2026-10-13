<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/test-gated-route', function () {
        return response()->json(['success' => true]);
    })->middleware(['auth', 'permission:properties.create']);
});

test('super admin can create a custom role with audit trail', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->post(route('dashboard.roles.store'), [
            'name' => 'Field Inspector',
            'description' => 'Inspects properties on-site across Addis Ababa',
            'reason' => 'Created for municipal compliance team',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('roles', [
        'name' => 'Field Inspector',
        'slug' => 'field_inspector',
    ]);

    $role = Role::where('slug', 'field_inspector')->first();
    expect($role)->not->toBeNull();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'role.created',
        'entity_type' => 'Role',
        'entity_id' => (string) $role->id,
    ]);
});

test('updating role permissions writes diff and reason to audit_logs', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $permission1 = Permission::firstOrCreate(
        ['key' => 'properties.create'],
        ['label' => 'Create Properties', 'group' => 'property_owner']
    );
    $permission2 = Permission::firstOrCreate(
        ['key' => 'properties.view'],
        ['label' => 'View Properties', 'group' => 'property_owner']
    );

    $role = Role::firstOrCreate(
        ['slug' => 'property_owner'],
        ['name' => 'Property Owner', 'is_system' => true]
    );

    $response = $this->actingAs($admin)
        ->post(route('dashboard.roles.permissions.update'), [
            'persona' => 'property_owner',
            'permission_ids' => [$permission1->id, $permission2->id],
            'reason' => 'Annual permission alignment audit',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    expect(RolePermission::where('role', 'property_owner')->pluck('permission_id')->all())
        ->toContain($permission1->id, $permission2->id);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'role.permissions_updated',
        'entity_type' => 'RolePermission',
        'reason' => 'Annual permission alignment audit',
    ]);
});

test('admin can toggle dashboard access and revoked user is denied dashboard.view', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer', 'dashboard_access_enabled' => true]);

    expect($user->hasPermission('dashboard.view'))->toBeTrue();

    $response = $this->actingAs($admin)
        ->post(route('dashboard.users.toggle-dashboard', $user), [
            'reason' => 'Temporary security block',
        ]);

    $response->assertRedirect();
    $user->refresh();

    expect($user->dashboard_access_enabled)->toBeFalse()
        ->and($user->hasPermission('dashboard.view'))->toBeFalse();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'dashboard.access_toggled',
        'entity_id' => (string) $user->id,
    ]);
});

test('admin can toggle media access and disabled user is denied media.view', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer', 'media_access_enabled' => true]);

    expect($user->hasPermission('media.view'))->toBeTrue();

    $response = $this->actingAs($admin)
        ->post(route('dashboard.users.toggle-media', $user), [
            'reason' => 'Copyright violation inquiry',
        ]);

    $response->assertRedirect();
    $user->refresh();

    expect($user->media_access_enabled)->toBeFalse()
        ->and($user->hasPermission('media.view'))->toBeFalse();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'media.access_toggled',
        'entity_id' => (string) $user->id,
    ]);
});

test('admin can suspend a user and suspended user is blocked from all permissions and routes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'agent', 'status' => 'active']);

    $response = $this->actingAs($admin)
        ->post(route('dashboard.users.status', $user), [
            'status' => 'suspended',
            'reason' => 'Policy violation under review',
        ]);

    $response->assertRedirect();
    $user->refresh();

    expect($user->status)->toBe('suspended')
        ->and($user->hasPermission('properties.view'))->toBeFalse()
        ->and($user->hasPermission('properties.create'))->toBeFalse();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'user.status_updated',
        'entity_id' => (string) $user->id,
        'reason' => 'Policy violation under review',
    ]);

    // Gated route returns 403
    $this->actingAs($user)
        ->getJson('/test-gated-route')
        ->assertStatus(403);
});

test('middleware EnsureUserHasPermission enforces granular permissions on routes', function () {
    $perm = Permission::firstOrCreate(
        ['key' => 'properties.create'],
        ['label' => 'Create Properties', 'group' => 'property_owner']
    );

    $unauthorizedUser = User::factory()->create(['role' => 'tenant']);
    // Deny permission explicitly
    RolePermission::where('role', 'tenant')->where('permission_id', $perm->id)->delete();

    $this->actingAs($unauthorizedUser)
        ->getJson('/test-gated-route')
        ->assertStatus(403);

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)
        ->getJson('/test-gated-route')
        ->assertStatus(200);
});
