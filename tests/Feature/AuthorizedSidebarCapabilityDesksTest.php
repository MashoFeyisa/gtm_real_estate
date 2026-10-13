<?php

use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('property manager with granted permissions can access all capability desks without 403', function (string $routeName) {
    $user = User::factory()->create(['role' => 'property_manager']);

    $allPermIds = Permission::query()->pluck('id')->all();
    $rows = array_map(fn ($pid) => [
        'user_id' => $user->id,
        'permission_id' => $pid,
        'granted' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ], $allPermIds);

    UserPermission::insert($rows);
    UserPermissionOverride::insert($rows);

    $response = $this->actingAs($user)->get(route($routeName));

    $response->assertSuccessful();
})->with([
    'seller dashboard' => 'seller.dashboard',
    'host dashboard' => 'host.dashboard',
    'developer portal' => 'developer.portal',
    'property manager dashboard' => 'property-manager.dashboard',
    'tenant dashboard' => 'tenant.dashboard',
    'finance desk' => 'finance.index',
    'agent portal' => 'agent.portal',
    'company dashboard' => 'company.dashboard',
    'moderator dashboard' => 'moderator.dashboard',
    'verification officer dashboard' => 'verification-officer.dashboard',
    'customer support dashboard' => 'customer-support.dashboard',
    'cleaner dashboard' => 'cleaner.dashboard',
    'professional dashboard' => 'professional.dashboard',
    'investor dashboard' => 'investor.dashboard',
    'campaign dashboard' => 'campaign.dashboard',
    'buyer dashboard' => 'buyer.dashboard',
    'admin command desk' => 'dashboard',
    'universal persona desk' => 'role.portal',
]);

test('users with custom granted permissions can access universal persona desk', function () {
    $user = User::factory()->create(['role' => 'buyer']);

    // Plain buyer gets 403 on universal persona desk
    $this->actingAs($user)->get(route('role.portal'))->assertForbidden();

    // Now admin grants them a permission
    $permId = Permission::query()->where('key', 'maintenance.view')->value('id');
    UserPermission::create(['user_id' => $user->id, 'permission_id' => $permId, 'granted' => true]);
    UserPermissionOverride::create(['user_id' => $user->id, 'permission_id' => $permId, 'granted' => true]);

    // Now they can access the universal persona desk
    $response = $this->actingAs($user->fresh())->get(route('role.portal'));
    $response->assertSuccessful();
    $response->assertSee('Maintenance');
});

test('unauthorized users get 403 on restricted capability desks', function (string $routeName) {
    $user = User::factory()->create(['role' => 'buyer']);

    $response = $this->actingAs($user)->get(route($routeName));

    $response->assertForbidden();
})->with([
    'admin command desk' => 'dashboard',
    'finance desk' => 'finance.index',
    'agent portal' => 'agent.portal',
    'company dashboard' => 'company.dashboard',
    'campaign dashboard' => 'campaign.dashboard',
    'developer portal' => 'developer.portal',
    'moderator dashboard' => 'moderator.dashboard',
    'verification officer dashboard' => 'verification-officer.dashboard',
    'cleaner dashboard' => 'cleaner.dashboard',
    'universal persona desk' => 'role.portal',
]);
