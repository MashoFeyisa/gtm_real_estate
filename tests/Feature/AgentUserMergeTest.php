<?php

use App\Models\Agent;
use App\Models\Property;
use App\Models\User;

test('creating a user with the agent role through the dashboard fills the agent profile', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post('/dashboard/users', [
            'name' => 'Merged Agent',
            'email' => 'merged.agent@example.com',
            'password' => 'Secret123!',
            'role' => 'agent',
            'phone' => '+251911223344',
            'bio' => 'Luxury villa specialist',
            'tier' => 'senior',
            'split_percentage' => 65,
            'license_number' => 'ETH-BRK-2026-042',
            'license_verified' => '1',
            'commission_rate' => 7.5,
            'is_active' => '1',
        ])
        ->assertRedirect(route('dashboard', ['section' => 'users']));

    $user = User::where('email', 'merged.agent@example.com')->first();
    expect($user)->not->toBeNull();

    $agent = Agent::where('email', 'merged.agent@example.com')->first();

    expect($agent)->not->toBeNull()
        ->and($agent->user_id)->toBe($user->id)
        ->and($agent->phone)->toBe('+251911223344')
        ->and($agent->bio)->toBe('Luxury villa specialist')
        ->and($agent->tier)->toBe('senior')
        ->and((float) $agent->split_percentage)->toBe(65.0)
        ->and($agent->license_number)->toBe('ETH-BRK-2026-042')
        ->and($agent->license_verified)->toBeTrue()
        ->and((float) $agent->commission_rate)->toBe(7.5)
        ->and($agent->is_active)->toBeTrue();
});

test('agent creation without profile fields falls back to tier defaults', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post('/dashboard/users', [
            'name' => 'Default Agent',
            'email' => 'default.agent@example.com',
            'password' => 'Secret123!',
            'role' => 'agent',
        ])
        ->assertRedirect();

    $agent = Agent::where('email', 'default.agent@example.com')->first();

    expect($agent)->not->toBeNull()
        ->and($agent->tier)->toBe('junior')
        ->and((float) $agent->split_percentage)->toBe(60.0)
        ->and((float) $agent->commission_rate)->toBe(5.0)
        ->and($agent->is_active)->toBeTrue()
        ->and($agent->license_verified)->toBeFalse();
});

test('updating a user syncs submitted agent profile fields and partial updates preserve them', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post('/dashboard/users', [
            'name' => 'Merged Agent',
            'email' => 'merged.agent@example.com',
            'password' => 'Secret123!',
            'role' => 'agent',
            'phone' => '+251900000001',
            'commission_rate' => 6,
        ])
        ->assertRedirect();

    $user = User::where('email', 'merged.agent@example.com')->first();

    // Full edit from the merged user form: agent fields are submitted and synced.
    $this->actingAs($admin)
        ->put(route('dashboard.users.update', $user), [
            'name' => 'Merged Agent Renamed',
            'email' => 'merged.agent@example.com',
            'role' => 'agent',
            'phone' => '+251900000002',
            'bio' => 'Updated specialty',
            'tier' => 'principal',
            'split_percentage' => 82,
            'license_number' => 'ETH-BRK-2026-099',
            'license_verified' => '1',
            'commission_rate' => 8.25,
            'is_active' => '1',
        ])
        ->assertRedirect(route('dashboard', ['section' => 'users']));

    $agent = $user->agentProfile()->first();

    expect($agent->name)->toBe('Merged Agent Renamed')
        ->and($agent->phone)->toBe('+251900000002')
        ->and($agent->bio)->toBe('Updated specialty')
        ->and($agent->tier)->toBe('principal')
        ->and((float) $agent->split_percentage)->toBe(82.0)
        ->and($agent->license_number)->toBe('ETH-BRK-2026-099')
        ->and($agent->license_verified)->toBeTrue()
        ->and((float) $agent->commission_rate)->toBe(8.25)
        ->and($agent->is_active)->toBeTrue();

    // Partial update without agent fields (role/permission change only) keeps the profile intact.
    $this->actingAs($admin)
        ->put(route('dashboard.users.update', $user), [
            'name' => 'Merged Agent Renamed',
            'email' => 'merged.agent@example.com',
            'role' => 'agent',
        ])
        ->assertRedirect();

    $agent->refresh();

    expect($agent->phone)->toBe('+251900000002')
        ->and($agent->bio)->toBe('Updated specialty')
        ->and($agent->tier)->toBe('principal')
        ->and($agent->license_verified)->toBeTrue()
        ->and($agent->is_active)->toBeTrue()
        ->and((float) $agent->commission_rate)->toBe(8.25);
});

test('updating a user no longer clears the assigned property hub', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $property = Property::create([
        'title' => 'Atlas Reception Hub',
        'slug' => 'atlas-reception-hub',
        'type' => 'stay',
        'property_category' => 'guest_house',
        'price' => 3500,
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanealem, Atlas',
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'role' => 'receptionist',
        'assigned_property_id' => $property->id,
    ]);

    $this->actingAs($admin)
        ->put(route('dashboard.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'receptionist',
        ])
        ->assertRedirect();

    expect($user->fresh()->assigned_property_id)->toBe($property->id);
});

test('dashboard sidebar no longer links to a standalone agents section', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('data-target-section="agents"', false)
        ->assertSee('data-target-section="users"', false);
});
