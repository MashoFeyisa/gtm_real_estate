<?php

use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('login lands each role on its own dashboard', function (string $role, string $expectedRoute) {
    $user = User::create([
        'name' => 'Role Member',
        'email' => $role.'@example.com',
        'password' => Hash::make('password123'),
        'role' => $role,
    ]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect(route($expectedRoute));
})->with([
    'property owner (seller dashboard)' => ['property_owner', 'seller.dashboard'],
    'moderator (moderator dashboard)' => ['moderator', 'moderator.dashboard'],
    'verification officer (verification dashboard)' => ['verification_officer', 'verification-officer.dashboard'],
    'buyer (buyer dashboard)' => ['buyer', 'buyer.dashboard'],
    'legacy client (client portal)' => ['client', 'client.portal'],
    'finance officer (finance desk)' => ['finance_officer', 'finance.index'],
    'receptionist (front desk)' => ['receptionist', 'receptionist.dashboard'],
    'agent (agent portal)' => ['agent', 'agent.portal'],
    'campaign officer (campaign dashboard)' => ['campaign_officer', 'campaign.dashboard'],
    'real estate company (company dashboard)' => ['real_estate_company', 'company.dashboard'],
    'admin (admin dashboard)' => ['admin', 'dashboard'],
]);

test('client portal greets the signed in role in the sidebar', function () {
    $this->seed(RolePermissionSeeder::class);

    $buyer = User::create([
        'name' => 'Buyer Bereket',
        'email' => 'bereket.buyer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $response = $this->actingAs($buyer)->get(route('client.portal'));

    $response->assertOk();
    $response->assertSee('Signed in');
    $response->assertSee('Buyer Bereket');
    $response->assertSee('Buyer');
});

test('legacy client accounts resolve to the buyer role label', function () {
    $this->seed(RolePermissionSeeder::class);

    $client = User::create([
        'name' => 'Legacy Client',
        'email' => 'legacy.client@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $response = $this->actingAs($client)->get(route('client.portal'));

    $response->assertOk();
    $response->assertSee('Buyer');
});

test('client portal still renders after a capability is revoked', function () {
    $this->seed(RolePermissionSeeder::class);

    $buyer = User::create([
        'name' => 'Buyer Hana',
        'email' => 'hana.buyer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    UserPermissionOverride::create([
        'user_id' => $buyer->id,
        'permission_id' => Permission::query()->where('key', 'offers.create')->value('id'),
        'granted' => false,
    ]);

    $response = $this->actingAs($buyer)->get(route('client.portal'));

    $response->assertOk();
    $response->assertSee('Signed in');
    $response->assertDontSee('Your Capabilities');
});
