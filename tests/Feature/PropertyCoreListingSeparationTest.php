<?php

use App\Models\Agent;
use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Module 1 Agent',
        'email' => 'module1.agent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->user->id,
        'name' => 'Ethio Prime Realty',
        'email' => 'ethioprime@example.com',
        'phone' => '+251 91 100 2233',
        'is_active' => true,
    ]);
});

test('property automatically generates ethiopian property_code conforming to SRS specifications', function () {
    $property = Property::create([
        'title' => 'Bole Brass Luxury Villa',
        'slug' => 'bole-brass-luxury-villa',
        'description' => 'Architectural masterpiece in Bole Brass.',
        'price' => 28000000.00,
        'bedrooms' => 5,
        'bathrooms' => 4,
        'area' => 500,
        'type' => 'sale',
        'property_category' => 'villa',
        'city' => 'Addis Ababa',
        'address' => 'Bole Brass, Near Japanese Embassy',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $this->agent->id,
    ]);

    expect($property->property_code)->not->toBeNull()
        ->and($property->property_code)->toStartWith('ETH-')
        ->and($property->property_code)->toContain('AA-')
        ->and($property->property_code)->toContain('-VIL-');
});

test('property auto-syncs primary commercial listing for backwards compatibility', function () {
    $property = Property::create([
        'title' => 'Kazanchis Executive Apartment',
        'slug' => 'kazanchis-executive-apartment',
        'description' => 'Modern apartment in central financial district.',
        'price' => 65000.00,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 120,
        'type' => 'rent',
        'property_category' => 'apartment',
        'city' => 'Addis Ababa',
        'address' => 'Kazanchis, Intercontinental Hotel Area',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $this->agent->id,
    ]);

    $listings = $property->listings()->get();
    expect($listings)->toHaveCount(1);

    $primaryListing = $listings->first();
    expect($primaryListing->listing_type)->toBe('rent')
        ->and((float) $primaryListing->price)->toEqual(65000.00)
        ->and($primaryListing->pricing_period)->toBe('monthly')
        ->and($primaryListing->listing_code)->toStartWith('LST-RENT-');
});

test('property master entity can host multiple distinct active commercial listings', function () {
    // Master Asset
    $property = Property::create([
        'title' => 'Old Airport Diplomatic Residence',
        'slug' => 'old-airport-diplomatic-residence',
        'description' => 'G+2 Luxury Villa with private compound.',
        'price' => 35000000.00,
        'bedrooms' => 6,
        'bathrooms' => 5,
        'area' => 650,
        'building_area' => 520,
        'floors' => 3,
        'parking_spaces' => 4,
        'type' => 'sale',
        'property_category' => 'villa',
        'city' => 'Addis Ababa',
        'address' => 'Old Airport, Golf Club Area',
        'status' => 'published',
        'is_active' => true,
        'has_electricity' => true,
        'has_water' => true,
        'has_generator' => true,
        'has_water_tank' => true,
        'has_solar' => true,
        'has_security' => true,
        'agent_id' => $this->agent->id,
    ]);

    // Create additional commercial listings on same property
    $property->createListing([
        'listing_type' => 'rent',
        'title' => 'Diplomatic Long-Term Lease',
        'price' => 180000.00,
        'currency' => 'ETB',
        'pricing_period' => 'monthly',
        'deposit_amount' => 540000.00,
        'status' => 'active',
    ]);

    $property->createListing([
        'listing_type' => 'stay',
        'title' => 'Old Airport Executive Villa Stay',
        'price' => 14000.00,
        'currency' => 'ETB',
        'pricing_period' => 'nightly',
        'cleaning_fee' => 1500.00,
        'minimum_stay' => 2,
        'status' => 'active',
    ]);

    $property->refresh();

    expect($property->listings()->count())->toBe(3)
        ->and($property->activeListings()->count())->toBe(3)
        ->and($property->saleListing())->not->toBeNull()
        ->and($property->rentListing())->not->toBeNull()
        ->and($property->stayListing())->not->toBeNull();

    // Test Listing Scopes
    expect(Listing::forSale()->count())->toBeGreaterThanOrEqual(1)
        ->and(Listing::forRent()->count())->toBeGreaterThanOrEqual(1)
        ->and(Listing::forStay()->count())->toBeGreaterThanOrEqual(1);
});

test('public property view displays master ethiopian property id, specs, utilities, and multi-listing options', function () {
    $property = Property::create([
        'title' => 'CMC Summit Luxury Villa',
        'slug' => 'cmc-summit-luxury-villa',
        'description' => 'Beautiful family villa in secure CMC compound.',
        'price' => 24000000.00,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 380,
        'building_area' => 320,
        'floors' => 2,
        'parking_spaces' => 3,
        'type' => 'sale',
        'property_category' => 'villa',
        'city' => 'Addis Ababa',
        'address' => 'CMC Summit',
        'status' => 'published',
        'is_active' => true,
        'has_electricity' => true,
        'has_water' => true,
        'has_generator' => true,
        'has_water_tank' => true,
        'has_solar' => false,
        'has_security' => true,
        'agent_id' => $this->agent->id,
    ]);

    // Attach a rental listing too
    $property->createListing([
        'listing_type' => 'rent',
        'title' => 'Rental Option',
        'price' => 75000.00,
        'currency' => 'ETB',
        'pricing_period' => 'monthly',
        'status' => 'active',
    ]);

    $response = $this->get(route('properties.show', $property->slug));

    $response->assertStatus(200);
    // Ethiopian Property ID
    $response->assertSee($property->property_code, false);
    // Multi-listing notification
    $response->assertSee('Listing Modes Active', false);
    // Physical Specs & Utilities
    $response->assertSee('Physical Specifications & Utilities', false);
    $response->assertSee('320 sqm', false);
    $response->assertSee('3 Vehicles', false);
    $response->assertSee('3-Phase / Electric', false);
    $response->assertSee('Municipal Water Line', false);
    $response->assertSee('Water Reservoir Tank', false);
    $response->assertSee('Backup Standby Generator', false);
    $response->assertSee('24/7 Gated Security', false);
});

test('adding a property with module 1 specifications displays all architectural parameters and utility readiness', function () {
    $adminUser = User::create([
        'name' => 'Admin User',
        'email' => 'admin.specs@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $postData = [
        'title' => 'Bole Medhanialem Grand Residence',
        'description' => 'A premier residential asset with complete utility readiness.',
        'price' => 22000000,
        'bedrooms' => 3,
        'bathrooms' => 3,
        'area' => 400,
        'building_area' => 400,
        'floors' => 1,
        'parking_spaces' => 1,
        'condition' => 'excellent',
        'type' => 'sale',
        'category' => 'apartment',
        'status' => 'published',
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanialem',
        'has_electricity' => '1',
        'has_water' => '1',
        'has_water_tank' => '1',
        'has_security' => '1',
        // Note: has_generator and has_solar are not checked/submitted (false)
    ];

    $response = $this->actingAs($adminUser)->post(route('dashboard.properties.store'), $postData);
    $response->assertRedirect(route('dashboard'));

    $createdProperty = Property::where('title', 'Bole Medhanialem Grand Residence')->first();
    expect($createdProperty)->not->toBeNull()
        ->and($createdProperty->building_area)->toBe(400)
        ->and($createdProperty->floors)->toBe(1)
        ->and($createdProperty->parking_spaces)->toBe(1)
        ->and($createdProperty->condition)->toBe('excellent')
        ->and($createdProperty->has_electricity)->toBeTrue()
        ->and($createdProperty->has_water)->toBeTrue()
        ->and($createdProperty->has_water_tank)->toBeTrue()
        ->and($createdProperty->has_generator)->toBeFalse()
        ->and($createdProperty->has_solar)->toBeFalse()
        ->and($createdProperty->has_security)->toBeTrue()
        ->and($createdProperty->property_code)->toStartWith('ETH-AA-BOLE-APT-');

    // Verify public show view displays all elements exactly as specified
    $showResponse = $this->get(route('properties.show', $createdProperty->slug));
    $showResponse->assertStatus(200);

    // Section title & subtitle
    $showResponse->assertSee('Physical Specifications & Utilities', false);
    $showResponse->assertSee('Verified architectural parameters and infrastructure readiness', false);

    // Master Asset ID
    $showResponse->assertSee('Master Asset: '.$createdProperty->property_code, false);

    // Architectural Specs
    $showResponse->assertSee('Building Area', false);
    $showResponse->assertSee('400 sqm', false);
    $showResponse->assertSee('Stories / Floors', false);
    $showResponse->assertSee('G+0 (1 levels)', false);
    $showResponse->assertSee('Parking Spaces', false);
    $showResponse->assertSee('1 Vehicles', false);
    $showResponse->assertSee('Asset Condition', false);
    $showResponse->assertSee('excellent', false);

    // Utility Readiness
    $showResponse->assertSee('Ethiopian Utility Readiness', false);
    $showResponse->assertSee('3-Phase / Electric', false);
    $showResponse->assertSee('Municipal Water Line', false);
    $showResponse->assertSee('Water Reservoir Tank', false);
    $showResponse->assertSee('Generator Ready', false);
    $showResponse->assertSee('Solar Compatible', false);
    $showResponse->assertSee('24/7 Gated Security', false);
});
