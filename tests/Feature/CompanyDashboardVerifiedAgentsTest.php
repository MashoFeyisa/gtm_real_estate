<?php

use App\Models\Agent;
use App\Models\Order;
use App\Models\Property;
use App\Models\User;

test('company dashboard KPI and badges read the license_verified column', function () {
    $companyUser = User::factory()->create(['role' => 'company']);

    // Names deliberately avoid the badge wording so assertSee matches only
    // what the view renders, never the fixture data.
    Agent::create([
        'user_id' => User::factory()->create(['role' => 'agent'])->id,
        'name' => 'Broker Tsegaye Licensed',
        'email' => 'licensed.broker@example.com',
        'phone' => '+251911000071',
        'is_active' => true,
        'license_verified' => true,
    ]);

    Agent::create([
        'user_id' => User::factory()->create(['role' => 'agent'])->id,
        'name' => 'Broker Mulu Pending',
        'email' => 'pending.broker@example.com',
        'phone' => '+251911000072',
        'is_active' => true,
        'license_verified' => false,
    ]);

    $response = $this->actingAs($companyUser)->get(route('company.dashboard'));

    // On MySQL the old is_verified query crashed with SQLSTATE[42S22];
    // on sqlite it silently counted zero and badged every broker as pending.
    $response->assertOk();
    $response->assertSee('Verified Broker');
    $response->assertSee('Pending KYC');
    $response->assertSee('1 Verified');
});

test('company dashboard grossSalesVolume does not crash with property_price column error when orders exist', function () {
    $companyUser = User::factory()->create(['role' => 'company']);

    $property = Property::create([
        'title' => 'Bole Medhanialem Luxury Penthouse',
        'slug' => 'bole-medhanialem-luxury-penthouse',
        'price' => 25000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 320,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
    ]);

    Order::create([
        'property_id' => $property->id,
        'name' => 'Abebe Kebede',
        'email' => 'abebe@example.com',
        'phone' => '+251911223344',
        'offer_amount' => 24000000,
        'status' => 'accepted',
    ]);

    $response = $this->actingAs($companyUser)->get(route('company.dashboard'));

    $response->assertOk();
    $response->assertSee('24,000,000');
});
