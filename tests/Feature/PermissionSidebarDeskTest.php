<?php

use App\Models\Permission;
use App\Models\Property;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

/*
|--------------------------------------------------------------------------
| 1. Non-Admin Universal Persona Desk & Admin Link Exclusion
|--------------------------------------------------------------------------
*/

test('non-admin user never sees Universal Persona Desk or any admin links in sidebar', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    $response = $this->actingAs($user)->get($user->dashboard_url);
    $response->assertOk();

    // Must never see Universal Persona Desk
    $response->assertDontSee('Universal Persona Desk', false);
    $response->assertDontSee('Universal Desk', false);
    $response->assertDontSee(route('role.portal'), false);

    // Must never see Admin Command Desk
    $response->assertDontSee('Admin Command Desk', false);
    $response->assertDontSee(route('dashboard'), false);

    // Default dashboard should NOT show Granted Access section when no extra permissions are granted
    $response->assertDontSee('Granted Access', false);
})->with([
    'buyer' => 'buyer',
    'host' => 'host',
    'agent' => 'agent',
]);

test('non-admin user gets 403 when trying to access admin dashboard or persona portal directly', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    // Admin dashboard /dashboard
    $this->actingAs($user)->get(route('dashboard'))->assertForbidden();

    // Universal persona desk /persona-portal
    $this->actingAs($user)->get(route('role.portal'))->assertForbidden();
})->with([
    'buyer' => 'buyer',
    'host' => 'host',
    'agent' => 'agent',
]);

/*
|--------------------------------------------------------------------------
| 2. Buyer Workflow: Grant & Revoke property.create
|--------------------------------------------------------------------------
*/

test('buyer: base menu is kept, granted property.create adds Post Property, revoking removes it with 403', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    // 1. Initial state: Buyer has base menu, no Granted Access section
    $response = $this->actingAs($buyer)->get(route('buyer.dashboard'));
    $response->assertOk();
    $response->assertSee('Overview', false);
    $response->assertSee('data-target-section="saved-searches"', false);
    $response->assertSee('data-target-section="offers"', false);
    $response->assertSee('data-target-section="viewings"', false);
    $response->assertDontSee('Granted Access', false);
    $response->assertDontSee('Post Property', false);

    // Visiting user.properties.create without permission gives 403
    $this->actingAs($buyer)->get(route('user.properties.create'))->assertForbidden();

    // 2. Admin grants property.create to this Buyer
    $permId = Permission::where('key', 'property.create')->value('id');
    UserPermissionOverride::create([
        'user_id' => $buyer->id,
        'permission_id' => $permId,
        'granted' => true,
    ]);

    // 3. Buyer refreshes dashboard: "Granted Access" appears with "Post Property"
    $responseGranted = $this->actingAs($buyer->fresh())->get(route('buyer.dashboard'));
    $responseGranted->assertOk();
    $responseGranted->assertSee('Granted Access', false);
    $responseGranted->assertSee('Post Property', false);
    $responseGranted->assertSee(route('user.properties.create'), false);

    // 4. Working page: Buyer opens Post Property page and submits a property
    $createPage = $this->actingAs($buyer->fresh())->get(route('user.properties.create'));
    $createPage->assertOk();
    $createPage->assertSee('Post New Property');

    $postResponse = $this->actingAs($buyer->fresh())->post(route('user.properties.store'), [
        'title' => 'Bole Medhanealem Modern Penthouse',
        'price' => 12500000,
        'type' => 'sale',
        'city' => 'Addis Ababa',
        'description' => 'Spacious luxury penthouse near the cathedral.',
    ]);
    $postResponse->assertRedirect();

    $this->assertDatabaseHas('properties', [
        'user_id' => $buyer->id,
        'title' => 'Bole Medhanealem Modern Penthouse',
        'status' => 'draft',
    ]);

    // 5. Admin revokes property.create
    UserPermissionOverride::where('user_id', $buyer->id)->where('permission_id', $permId)->update(['granted' => false]);

    // 6. Buyer refreshes dashboard: Post Property and Granted Access section disappear
    $responseRevoked = $this->actingAs($buyer->fresh())->get(route('buyer.dashboard'));
    $responseRevoked->assertOk();
    $responseRevoked->assertDontSee('Post Property', false);
    $responseRevoked->assertDontSee('Granted Access', false);

    // 7. Visiting URL returns 403 Forbidden
    $this->actingAs($buyer->fresh())->get(route('user.properties.create'))->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| 3. Host Workflow: Grant & Revoke offer.create
|--------------------------------------------------------------------------
*/

test('host: base menu is kept, granted offer.create adds My Offers, revoking removes it with 403', function () {
    $host = User::factory()->create(['role' => 'host']);

    // 1. Initial state: Host has base menu, no Granted Access section
    $response = $this->actingAs($host)->get(route('host.dashboard'));
    $response->assertOk();
    $response->assertSee('Overview', false);
    $response->assertSee('data-target-section="accommodations"', false);
    $response->assertDontSee('Granted Access', false);
    $response->assertDontSee('My Offers', false);

    // Visiting user.offers directly returns 403
    $this->actingAs($host)->get(route('user.offers'))->assertForbidden();

    // 2. Admin grants offer.create to this Host
    $permId = Permission::where('key', 'offer.create')->value('id');
    UserPermissionOverride::create([
        'user_id' => $host->id,
        'permission_id' => $permId,
        'granted' => true,
    ]);

    // 3. Host refreshes dashboard: "Granted Access" appears with "My Offers"
    $responseGranted = $this->actingAs($host->fresh())->get(route('host.dashboard'));
    $responseGranted->assertOk();
    $responseGranted->assertSee('Granted Access', false);
    $responseGranted->assertSee('My Offers', false);
    $responseGranted->assertSee(route('user.offers'), false);

    // 4. Working page: Host opens My Offers page
    $offersPage = $this->actingAs($host->fresh())->get(route('user.offers'));
    $offersPage->assertOk();
    $offersPage->assertSee('My Offers');

    // 5. Admin revokes offer.create
    UserPermissionOverride::where('user_id', $host->id)->where('permission_id', $permId)->update(['granted' => false]);

    // 6. Host refreshes: My Offers disappears, Granted Access section disappears
    $responseRevoked = $this->actingAs($host->fresh())->get(route('host.dashboard'));
    $responseRevoked->assertOk();
    $responseRevoked->assertDontSee('My Offers', false);
    $responseRevoked->assertDontSee('Granted Access', false);

    // 7. Visiting URL returns 403 Forbidden
    $this->actingAs($host->fresh())->get(route('user.offers'))->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| 4. Agent Workflow: Grant & Revoke booking.view
|--------------------------------------------------------------------------
*/

test('agent: base menu is kept, granted booking.view adds My Bookings, revoking removes it with 403', function () {
    $agentUser = User::factory()->create(['role' => 'agent']);
    $agentUser->ensureAgentProfile();

    // 1. Initial state: Agent has CRM base menu, no Granted Access section
    $response = $this->actingAs($agentUser)->get(route('agent.portal'));
    $response->assertOk();
    $response->assertSee('Overview', false);
    $response->assertSee('data-target-section="leads-pipeline"', false);
    $response->assertDontSee('Granted Access', false);
    $response->assertDontSee('My Bookings', false);

    // Visiting user.bookings directly returns 403
    $this->actingAs($agentUser)->get(route('user.bookings'))->assertForbidden();

    // 2. Admin grants booking.view to this Agent
    $permId = Permission::where('key', 'booking.view')->value('id');
    UserPermissionOverride::create([
        'user_id' => $agentUser->id,
        'permission_id' => $permId,
        'granted' => true,
    ]);

    // 3. Agent refreshes dashboard: "Granted Access" appears with "My Bookings"
    $responseGranted = $this->actingAs($agentUser->fresh())->get(route('agent.portal'));
    $responseGranted->assertOk();
    $responseGranted->assertSee('Granted Access', false);
    $responseGranted->assertSee('My Bookings', false);
    $responseGranted->assertSee(route('user.bookings'), false);

    // 4. Working page: Agent opens My Bookings page
    $bookingsPage = $this->actingAs($agentUser->fresh())->get(route('user.bookings'));
    $bookingsPage->assertOk();
    $bookingsPage->assertSee('My Bookings');

    // 5. Admin revokes booking.view
    UserPermissionOverride::where('user_id', $agentUser->id)->where('permission_id', $permId)->update(['granted' => false]);

    // 6. Agent refreshes: My Bookings disappears, Granted Access section disappears
    $responseRevoked = $this->actingAs($agentUser->fresh())->get(route('agent.portal'));
    $responseRevoked->assertOk();
    $responseRevoked->assertDontSee('My Bookings', false);
    $responseRevoked->assertDontSee('Granted Access', false);

    // 7. Visiting URL returns 403 Forbidden
    $this->actingAs($agentUser->fresh())->get(route('user.bookings'))->assertForbidden();
});
