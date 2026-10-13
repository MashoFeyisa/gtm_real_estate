<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can register users with newly assignable roles: cleaner and investor', function (string $role) {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('dashboard.users.store'), [
        'name' => "Test {$role} User",
        'email' => "test_{$role}@example.com",
        'password' => 'Password123!',
        'role' => $role,
    ]);

    $response->assertRedirect(route('dashboard').'#users');
    $response->assertSessionHas('success');

    $created = User::where('email', "test_{$role}@example.com")->first();
    expect($created)->not->toBeNull();
    expect($created->role)->toBe($role);
    expect($created->roles()->where('slug', $role)->exists())->toBeTrue();
})->with(['cleaner', 'investor']);

test('campaign management permissions require campaign officer role for strict separation of duties', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $campaignOfficer = User::factory()->create(['role' => 'campaign_officer']);
    $buyer = User::factory()->create(['role' => 'buyer']);

    expect($campaignOfficer->canManageCampaigns())->toBeTrue();
    expect($admin->canManageCampaigns())->toBeFalse();
    expect($buyer->canManageCampaigns())->toBeFalse();
});

test('admin cannot open or act inside another user dashboard and impersonation is forbidden', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $targetUser = User::factory()->create(['role' => 'cleaner']);

    // 1. Impersonation is rejected with 403 Forbidden
    $response = $this->actingAs($admin)->post(route('dashboard.users.impersonate', $targetUser));
    $response->assertForbidden();

    // 2. Admin cannot open another user's personal dashboard directly
    $buyerDashboardResponse = $this->actingAs($admin)->get(route('buyer.dashboard'));
    $buyerDashboardResponse->assertForbidden();

    $tenantDashboardResponse = $this->actingAs($admin)->get(route('tenant.dashboard'));
    $tenantDashboardResponse->assertForbidden();
});

test('non-admin user cannot impersonate another user', function () {
    $regularUser = User::factory()->create(['role' => 'buyer']);
    $targetUser = User::factory()->create(['role' => 'tenant']);

    $response = $this->actingAs($regularUser)->post(route('dashboard.users.impersonate', $targetUser));

    $response->assertForbidden();
    expect(auth()->id())->toBe($regularUser->id);
});

test('login page displays demo accounts quick-fill drawer', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('Demo Accounts');
    $response->assertSee('admin@realestate.com');
    $response->assertSee('campaign@realestate.com');
    $response->assertSee('cleaner@realestate.com');
});
