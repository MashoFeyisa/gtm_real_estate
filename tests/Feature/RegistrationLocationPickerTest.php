<?php

use App\Models\Agent;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::create([
        'name' => 'Admin User',
        'email' => 'admin.maps@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->agentUser = User::create([
        'name' => 'Agent Selam',
        'email' => 'agent.maps@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Selam Real Estate Agency',
        'email' => 'selam.maps@example.com',
        'phone' => '+251 91 123 4567',
        'is_active' => true,
    ]);

    $this->propertyPayload = [
        'title' => 'Bole Map Pinned Residence',
        'description' => 'Registered with an exact map pin.',
        'price' => 18000000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 320,
        'type' => 'sale',
        'category' => 'apartment',
        'status' => 'published',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'address' => 'Bole Medhanialem',
    ];

    $this->projectPayload = [
        'name' => 'Bole Atlas Map Heights',
        'developer_name' => 'Atlas Construction',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'address' => 'Near Edna Mall',
        'project_type' => 'apartments',
        'construction_status' => 'under_construction',
        'overall_completion_percentage' => 20,
        'starting_price' => 12500000.00,
        'land_area_sqm' => 3200.00,
    ];
});

test('admin registers a property with map coordinates that are persisted', function () {
    $response = $this->actingAs($this->adminUser)->post(route('dashboard.properties.store'), array_merge($this->propertyPayload, [
        'latitude' => 9.0016000,
        'longitude' => 38.7844000,
    ]));

    $response->assertRedirect(route('dashboard'));

    $property = Property::where('title', 'Bole Map Pinned Residence')->first();

    expect($property)->not->toBeNull()
        ->and((float) $property->latitude)->toBe(9.0016)
        ->and((float) $property->longitude)->toBe(38.7844)
        ->and($property->coordinates)->toBe([9.0016, 38.7844]);
});

test('admin registers a development project with map coordinates that are persisted', function () {
    $response = $this->actingAs($this->adminUser)->post(route('developments.store'), array_merge($this->projectPayload, [
        'latitude' => 9.0205000,
        'longitude' => 38.7675000,
    ]));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $project = Project::where('name', 'Bole Atlas Map Heights')->first();

    expect($project)->not->toBeNull()
        ->and((float) $project->latitude)->toBe(9.0205)
        ->and((float) $project->longitude)->toBe(38.7675)
        ->and($project->coordinates)->toBe([9.0205, 38.7675]);
});

test('a development registered without a picked pin falls back to its sub-city centroid', function () {
    $this->actingAs($this->adminUser)->post(route('developments.store'), array_merge($this->projectPayload, [
        'subcity' => 'Kirkos',
    ]));

    $project = Project::where('name', 'Bole Atlas Map Heights')->first();

    expect($project->latitude)->toBeNull()
        ->and($project->coordinates)->toBe([9.012, 38.762]);
});

test('updating a development replaces its map coordinates', function () {
    $this->actingAs($this->adminUser)->post(route('developments.store'), array_merge($this->projectPayload, [
        'latitude' => 9.0016000,
        'longitude' => 38.7844000,
    ]));

    $project = Project::where('name', 'Bole Atlas Map Heights')->first();

    $this->actingAs($this->adminUser)
        ->put(route('developments.update', $project), array_merge($this->projectPayload, [
            'latitude' => 8.9950000,
            'longitude' => 38.7420000,
        ]))
        ->assertRedirect();

    $project->refresh();

    expect((float) $project->latitude)->toBe(8.995)
        ->and((float) $project->longitude)->toBe(38.742);
});

test('coordinates outside the valid ranges are rejected', function () {
    $this->actingAs($this->adminUser)
        ->post(route('dashboard.properties.store'), array_merge($this->propertyPayload, [
            'latitude' => 120,
            'longitude' => 38.7844,
        ]))
        ->assertSessionHasErrors('latitude');

    $this->actingAs($this->adminUser)
        ->post(route('developments.store'), array_merge($this->projectPayload, [
            'latitude' => 9.0016,
            'longitude' => 240,
        ]))
        ->assertSessionHasErrors('longitude');

    expect(Property::where('title', 'Bole Map Pinned Residence')->exists())->toBeFalse()
        ->and(Project::where('name', 'Bole Atlas Map Heights')->exists())->toBeFalse();
});

test('the admin property and development forms render the interactive map picker', function () {
    $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('admin-property-location-map', false)
        ->assertSee('admin-property-latitude', false)
        ->assertSee('create-project-location-map', false)
        ->assertSee('create-project-latitude', false)
        ->assertSee('edit-project-location-map', false)
        ->assertSee('data-location-picker', false)
        ->assertSee('name="latitude"', false)
        ->assertSee('name="longitude"', false)
        ->assertSee('unpkg.com/leaflet@1.9.4/dist/leaflet.js', false);
});

test('the agent portal property forms render the map picker with the current pin', function () {
    $this->actingAs($this->adminUser)->post(route('dashboard.properties.store'), array_merge($this->propertyPayload, [
        'agent_id' => $this->agent->id,
        'latitude' => 9.0250000,
        'longitude' => 38.8350000,
    ]));

    $property = Property::where('title', 'Bole Map Pinned Residence')->first();

    expect((float) $property->latitude)->toBe(9.025);

    $response = $this->actingAs($this->agentUser)->get(route('agent.portal', ['edit_property' => $property->id]));

    $response->assertOk()
        ->assertSee('create-property-location-map', false)
        ->assertSee('edit-property-location-map', false)
        ->assertSee('edit-property-latitude', false)
        ->assertSee('data-lat="9.025"', false)
        ->assertSee('unpkg.com/leaflet@1.9.4/dist/leaflet.js', false);
});
