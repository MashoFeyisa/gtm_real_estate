<?php

use App\Models\Agent;
use App\Models\Property;
use App\Models\User;
use App\Services\EthiopianGeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->agentUser = User::create([
        'name' => 'Agent Dawit',
        'email' => 'dawit@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Dawit Realty Ethiopia',
        'email' => 'dawit@example.com',
        'phone' => '+251 91 333 4455',
        'is_active' => true,
    ]);

    $this->boleProperty = Property::create([
        'property_code' => 'ETH-AA-BOL-00000001',
        'title' => 'Bole Medhanialem Luxury Penthouse',
        'slug' => 'bole-medhanialem-luxury-penthouse',
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
        'woreda' => 'Woreda 03',
        'neighborhood' => 'Medhanialem',
        'address' => 'Cameroon St, Near Edna Mall',
        'latitude' => 8.9985,
        'longitude' => 38.7865,
        'agent_id' => $this->agent->id,
    ]);

    $this->yekaProperty = Property::create([
        'property_code' => 'ETH-AA-YEK-00000001',
        'title' => 'Yeka Abado Hillside Villa',
        'slug' => 'yeka-abado-hillside-villa',
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
        'woreda' => 'Woreda 08',
        'neighborhood' => 'Abado',
        'address' => 'Yeka Mountain Foothills',
        'latitude' => 9.0320,
        'longitude' => 38.8150,
        'agent_id' => $this->agent->id,
    ]);
});

test('public properties index returns map data and subcities catalog', function () {
    $response = $this->get(route('properties.index'));

    $response->assertOk();
    $response->assertViewHas('subcities');
    $response->assertViewHas('mapProperties');

    $mapData = $response->viewData('mapProperties');
    expect($mapData)->not->toBeEmpty();

    $bolePin = $mapData->firstWhere('id', $this->boleProperty->id);
    expect($bolePin)->not->toBeNull()
        ->and($bolePin['subcity'])->toBe('Bole')
        ->and($bolePin['lat'])->toBe(8.9985)
        ->and($bolePin['lng'])->toBe(38.7865);
});

test('filters properties by Ethiopian subcity', function () {
    $response = $this->get(route('properties.index', ['subcity' => 'Bole']));

    $response->assertOk();
    $response->assertSee('Bole Medhanialem Luxury Penthouse');
    $response->assertDontSee('Yeka Abado Hillside Villa');
});

test('filters properties by woreda', function () {
    $response = $this->get(route('properties.index', ['woreda' => 'Woreda 08']));

    $response->assertOk();
    $response->assertSee('Yeka Abado Hillside Villa');
    $response->assertDontSee('Bole Medhanialem Luxury Penthouse');
});

test('dynamically computes amenity proximity, walk score, and transit score using geo service', function () {
    $geoService = app(EthiopianGeoService::class);

    $dist = $geoService->haversineDistance(8.9985, 38.7865, 8.9778, 38.7993);
    expect($dist)->toBeGreaterThan(1.0)->toBeLessThan(4.0);

    $amenities = $this->boleProperty->nearby_amenities_list;
    expect($amenities)->toBeArray()->not->toBeEmpty();

    expect(isset($amenities['shopping']) || isset($amenities['transit']))->toBeTrue();

    // Verify walk score and transit score
    expect($this->boleProperty->effective_walk_score)
        ->toBeGreaterThanOrEqual(40)
        ->toBeLessThanOrEqual(100);

    expect($this->boleProperty->effective_transit_score)
        ->toBeGreaterThanOrEqual(35)
        ->toBeLessThanOrEqual(100);

    expect($this->boleProperty->walk_score_badge)->toHaveKeys(['label', 'amharic', 'badge_class']);
});

test('renders neighborhood amenity radar and GIS taxonomy on property detail page', function () {
    $response = $this->get(route('properties.show', $this->boleProperty->slug));

    $response->assertOk();
    $response->assertSee('Explore Location & Neighborhood', false);
    $response->assertSee('Bole Sub-City (ክፍለ ከተማ)');
    $response->assertSee('Woreda 03');
    $response->assertSee('Medhanialem');
    $response->assertSee('data-amenities=', false);
});

test('agent can post property listing with subcity, woreda, neighborhood, and GPS coordinates', function () {
    $response = $this->actingAs($this->agentUser)->post(route('agent.properties.store'), [
        'title' => 'Kazanchis Executive Suite',
        'price' => 14000000.00,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 130,
        'type' => 'sale',
        'category' => 'apartment',
        'status' => 'published',
        'city' => 'Addis Ababa',
        'subcity' => 'Kirkos',
        'woreda' => 'Woreda 05',
        'neighborhood' => 'Kazanchis',
        'address' => 'Near UNECA & Interluxury',
        'latitude' => 9.0205,
        'longitude' => 38.7675,
    ]);

    $response->assertRedirect();

    $newProp = Property::where('title', 'Kazanchis Executive Suite')->first();
    expect($newProp)->not->toBeNull()
        ->and($newProp->subcity)->toBe('Kirkos')
        ->and($newProp->woreda)->toBe('Woreda 05')
        ->and($newProp->neighborhood)->toBe('Kazanchis')
        ->and($newProp->latitude)->toBe(9.0205)
        ->and($newProp->longitude)->toBe(38.7675);
});

test('agent can update property coordinates and subcity', function () {
    $response = $this->actingAs($this->agentUser)->put(route('agent.properties.update', $this->boleProperty), [
        'title' => 'Bole Medhanialem Luxury Penthouse (Updated)',
        'price' => 18500000.00,
        'bedrooms' => 3,
        'bathrooms' => 3,
        'area' => 240,
        'type' => 'sale',
        'category' => 'apartment',
        'status' => 'published',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'woreda' => 'Woreda 04',
        'neighborhood' => 'Atlas / Medhanialem',
        'address' => 'Near Atlas Traffic Light',
        'latitude' => 9.0010,
        'longitude' => 38.7850,
    ]);

    $response->assertRedirect();

    $this->boleProperty->refresh();
    expect($this->boleProperty->woreda)->toBe('Woreda 04')
        ->and($this->boleProperty->neighborhood)->toBe('Atlas / Medhanialem')
        ->and($this->boleProperty->latitude)->toBe(9.0010)
        ->and($this->boleProperty->longitude)->toBe(38.7850);
});
