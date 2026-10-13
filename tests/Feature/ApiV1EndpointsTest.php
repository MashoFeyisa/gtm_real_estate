<?php

use App\Models\ApiToken;
use App\Models\Property;
use App\Models\User;
use App\Services\DuplicateDetector;
use Database\Seeders\ExperienceSeeder;
use Database\Seeders\PropertyTaxonomySeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(PropertyTaxonomySeeder::class);
    $this->seed(ExperienceSeeder::class);

    $this->user = User::create([
        'name' => 'API Mobile Test User',
        'email' => 'api.user@gtmrealestate.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
        'status' => 'active',
    ]);

    $this->property = Property::create([
        'title' => 'Bole Medhanialem Luxury Flat',
        'slug' => 'bole-medhanialem-luxury-flat',
        'description' => 'Spacious modern luxury apartment near Bole Medhanialem cathedral.',
        'price' => 12500000.00,
        'type' => 'sale',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 165.0,
    ]);
});

test('mobile user can register and receive bearer token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Abebe New User',
        'email' => 'abebe.api@example.com',
        'password' => 'Password123#Secure',
        'role' => 'buyer',
        'device_name' => 'iPhone 15 Pro',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'success',
        'user' => ['id', 'name', 'email', 'role'],
        'token',
        'expires_at',
    ]);
    expect($response->json('success'))->toBeTrue();
});

test('mobile user can login and access protected profile with bearer token', function () {
    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'api.user@gtmrealestate.com',
        'password' => 'password123',
    ]);

    $loginResponse->assertOk();
    $token = $loginResponse->json('token');
    expect($token)->not->toBeNull();

    // Access protected endpoint
    $meResponse = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/v1/auth/me');
    $meResponse->assertOk();
    expect($meResponse->json('user.email'))->toBe('api.user@gtmrealestate.com');

    // Logout and verify revocation
    $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/v1/auth/logout');
    $logoutResponse->assertOk();

    $recheckResponse = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/v1/auth/me');
    $recheckResponse->assertStatus(401);
});

test('properties catalog API supports filtering and pagination', function () {
    $response = $this->getJson('/api/v1/properties?city=Addis+Ababa&subcity=Bole&category=apartment');

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'data',
        'pagination' => ['current_page', 'last_page', 'total'],
    ]);
    expect($response->json('pagination.total'))->toBeGreaterThanOrEqual(1);

    // Detail API
    $detailResponse = $this->getJson('/api/v1/properties/'.$this->property->id);
    $detailResponse->assertOk();
    expect($detailResponse->json('data.title'))->toBe('Bole Medhanialem Luxury Flat');
});

test('stays catalog and stay booking API work', function () {
    $stay = Property::create([
        'title' => 'Kazanchis Executive Stay',
        'slug' => 'kazanchis-executive-stay',
        'price' => 3500.00,
        'type' => 'stay',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'subcity' => 'Kirkos',
    ]);

    $listResponse = $this->getJson('/api/v1/stays');
    $listResponse->assertOk();

    // Authenticated stay booking
    $tokenData = ApiToken::createTokenForUser($this->user);
    $bookResponse = $this->withHeader('Authorization', 'Bearer '.$tokenData['token'])
        ->postJson('/api/v1/stays/book', [
            'property_id' => $stay->id,
            'check_in' => now()->addDays(2)->toDateString(),
            'check_out' => now()->addDays(5)->toDateString(),
            'guest_name' => 'Bethlehem Kebede',
            'guest_email' => 'bethlehem@example.com',
            'guest_phone' => '+251 91 100 2233',
            'guests_count' => 2,
        ]);

    $bookResponse->assertStatus(201);
    expect($bookResponse->json('success'))->toBeTrue();
    expect($bookResponse->json('data.nights'))->toBe(3);
});

test('algorithmic automated valuation model calculates fair market value', function () {
    $response = $this->postJson('/api/v1/ai/valuation', [
        'subcity' => 'Bole',
        'property_category' => 'apartment',
        'area_sqm' => 150,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'year_built' => 2024,
        'has_generator' => true,
        'has_water_tank' => true,
        'has_elevator' => true,
        'has_parking' => true,
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'model',
        'valuation' => [
            'fair_market_value_etb',
            'valuation_range_low_etb',
            'valuation_range_high_etb',
            'estimated_rate_per_sqm_etb',
            'confidence_score_percent',
        ],
    ]);
    expect($response->json('valuation.fair_market_value_etb'))->toBeGreaterThan(10000000);
});

test('duplicate detector flags identical listings with high risk score', function () {
    $detector = new DuplicateDetector;

    $candidate = [
        'title' => 'Bole Medhanialem Luxury Flat',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'price' => 12500000.00,
    ];

    $result = $detector->detectDuplicates($candidate);
    expect($result['is_duplicate'])->toBeTrue();
    expect($result['risk_score'])->toBeGreaterThanOrEqual(60);
    expect($result['matching_property_ids'])->toContain($this->property->id);
});

test('integration endpoints syndication, mortgage, and webhooks function correctly', function () {
    // Syndication feed
    $syndicationResponse = $this->getJson('/api/v1/syndication/listings.json');
    $syndicationResponse->assertOk();
    expect($syndicationResponse->json('publisher'))->toBe('GTM Real Estate Platform');

    // Mortgage referral
    $mortgageResponse = $this->postJson('/api/v1/mortgage/referral', [
        'applicant_name' => 'Kassahun Tadesse',
        'applicant_phone' => '+251 91 222 3344',
        'applicant_email' => 'kassahun@example.com',
        'monthly_income_etb' => 85000,
        'property_price_etb' => 12000000,
        'preferred_bank' => 'CBE',
    ]);
    $mortgageResponse->assertStatus(201);
    expect($mortgageResponse->json('success'))->toBeTrue();

    // Smart lock webhook
    $lockResponse = $this->postJson('/api/v1/webhooks/smart-lock', [
        'device_id' => 'LOCK-BOLE-04',
        'event' => 'unlocked',
    ]);
    $lockResponse->assertOk();

    // Utility bills webhook
    $utilityResponse = $this->postJson('/api/v1/webhooks/utility-bills', [
        'utility_provider' => 'ethiopian_electric_utility',
        'account_number' => 'EEU-9928374',
        'amount_paid_etb' => 1250.00,
        'transaction_reference' => 'EEU-TXN-2026-001',
    ]);
    $utilityResponse->assertOk();
});
