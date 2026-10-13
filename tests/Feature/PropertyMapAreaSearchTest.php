<?php

use App\Models\Agent;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->agentUser = User::create([
        'name' => 'Agent Dawit',
        'email' => 'dawit.area@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Dawit Realty Ethiopia',
        'email' => 'dawit.area@example.com',
        'phone' => '+251 91 333 4455',
        'is_active' => true,
    ]);

    $this->boleProperty = Property::create([
        'property_code' => 'ETH-AA-BOL-00000011',
        'title' => 'Bole Area Pinned Apartment',
        'slug' => 'bole-area-pinned-apartment',
        'price' => 18000000.00,
        'bedrooms' => 3,
        'bathrooms' => 3,
        'area' => 240,
        'type' => 'sale',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'latitude' => 8.9985,
        'longitude' => 38.7865,
        'agent_id' => $this->agent->id,
    ]);

    $this->yekaProperty = Property::create([
        'property_code' => 'ETH-AA-YEK-00000011',
        'title' => 'Yeka Area Pinned Villa',
        'slug' => 'yeka-area-pinned-villa',
        'price' => 22000000.00,
        'bedrooms' => 5,
        'bathrooms' => 4,
        'area' => 380,
        'type' => 'sale',
        'property_category' => 'villa',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'subcity' => 'Yeka',
        'latitude' => 9.0320,
        'longitude' => 38.8150,
        'agent_id' => $this->agent->id,
    ]);

    $this->unpinnedProperty = Property::create([
        'property_code' => 'ETH-AA-LID-00000011',
        'title' => 'Lideta Unpinned Condominium',
        'slug' => 'lideta-unpinned-condominium',
        'price' => 9000000.00,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 110,
        'type' => 'rent',
        'property_category' => 'condominium',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'subcity' => 'Lideta',
        'latitude' => null,
        'longitude' => null,
        'agent_id' => $this->agent->id,
    ]);
});

test('searching the map area only returns listings pinned inside the drawn box', function () {
    // A box around Bole only: 8.99,38.78 -> 9.01,38.79
    $response = $this->get(route('properties.index', ['map_bounds' => '8.990000,38.780000,9.010000,38.790000']));

    $response->assertOk()
        ->assertSee('Bole Area Pinned Apartment')
        ->assertDontSee('Yeka Area Pinned Villa')
        ->assertDontSee('Lideta Unpinned Condominium')
        ->assertSee('Map area filter active', false);
});

test('a listing without a map pin never appears inside a drawn area', function () {
    // Wide box that covers the whole city: only the unpinned listing is excluded.
    $response = $this->get(route('properties.index', ['map_bounds' => '8.800000,38.600000,9.200000,39.000000']));

    $response->assertOk()
        ->assertSee('Bole Area Pinned Apartment')
        ->assertSee('Yeka Area Pinned Villa')
        ->assertDontSee('Lideta Unpinned Condominium');
});

test('the map area filter combines with the existing search filters', function () {
    $response = $this->get(route('properties.index', [
        'map_bounds' => '8.800000,38.600000,9.200000,39.000000',
        'type' => 'rent',
    ]));

    $response->assertOk()
        ->assertDontSee('Bole Area Pinned Apartment')
        ->assertDontSee('Yeka Area Pinned Villa');
});

test('malformed or inverted map bounds are ignored instead of filtering everything out', function () {
    collect([
        'not,a,bounds',
        '8.99,38.78,9.01',
        '9.01,38.79,8.99,38.78',
        '-95,38.78,9.01,38.79',
    ])->each(function (string $bounds) {
        $this->get(route('properties.index', ['map_bounds' => $bounds]))
            ->assertOk()
            ->assertSee('Bole Area Pinned Apartment')
            ->assertDontSee('Map area filter active', false);
    });
});

test('the map reopens at the searched area centre and zoom', function () {
    $response = $this->get(route('properties.index', [
        'map_bounds' => '8.990000,38.780000,9.010000,38.790000',
        'map_center' => '9.0000000,38.7850000',
        'map_zoom' => '14',
    ]));

    $response->assertOk()
        ->assertSee('data-initial-center="9.0000000,38.7850000"', false)
        ->assertSee('data-initial-zoom="14"', false)
        ->assertSee('data-initial-bounds="[[8.99,38.78],[9.01,38.79]]"', false);
});

test('the map exposes the search this area control and keeps the area in the filter form', function () {
    $response = $this->get(route('properties.index', ['map_bounds' => '8.990000,38.780000,9.010000,38.790000']));

    $response->assertOk()
        ->assertSee('id="search-map-area-btn"', false)
        ->assertSee('Search this area')
        ->assertSee('name="map_bounds" value="8.99,38.78,9.01,38.79"', false)
        ->assertSee('Clear area filter');
});

test('an area with no pinned listings explains why the results are empty', function () {
    $response = $this->get(route('properties.index', ['map_bounds' => '8.500000,38.100000,8.600000,38.200000']));

    $response->assertOk()
        ->assertSee('No listings with a map pin inside the area you drew.')
        ->assertSee('Clear area filter');
});

test('the public development page plots the saved project coordinates', function () {
    $project = Project::create([
        'name' => 'Kazanchis Pinned Towers',
        'developer_name' => 'Kazanchis Developers',
        'city' => 'Addis Ababa',
        'subcity' => 'Kirkos',
        'address' => 'Kazanchis Business District',
        'project_type' => 'apartments',
        'construction_status' => 'under_construction',
        'starting_price' => 12000000.00,
        'status' => 'published',
        'latitude' => 9.0205000,
        'longitude' => 38.7675000,
    ]);

    $response = $this->get(route('developments.show', $project->slug));

    $response->assertOk()
        ->assertSee('Project Location &amp; Access', false)
        ->assertSee('id="project-location-map"', false)
        ->assertSee('data-lat="9.0205"', false)
        ->assertSee('data-lng="38.7675"', false)
        ->assertDontSee('This project has no exact map pin yet');
});

test('a development without a pin shows its area centre and says so', function () {
    $project = Project::create([
        'name' => 'Kirkos Unpinned Residency',
        'developer_name' => 'Kirkos Developers',
        'city' => 'Addis Ababa',
        'subcity' => 'Kirkos',
        'project_type' => 'apartments',
        'construction_status' => 'under_construction',
        'starting_price' => 10000000.00,
        'status' => 'published',
    ]);

    $response = $this->get(route('developments.show', $project->slug));

    $response->assertOk()
        ->assertSee('data-coordinates-source="area"', false)
        ->assertSee('data-lat="9.012"', false)
        ->assertSee('This project has no exact map pin yet');
});
