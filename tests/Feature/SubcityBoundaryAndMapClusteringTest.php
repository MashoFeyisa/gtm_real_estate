<?php

use App\Models\Agent;
use App\Models\Property;
use App\Models\PropertyViewing;
use App\Models\SubcityBoundary;
use App\Models\User;
use App\Services\EthiopianDateService;
use App\Services\EthiopianGeoService;
use Carbon\Carbon;
use Database\Seeders\SubcityBoundarySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SubcityBoundarySeeder::class);
});

test('subcity polygon boundaries are seeded with valid coordinates and amharic names', function () {
    $bole = SubcityBoundary::query()->where('name', 'Bole')->first();

    expect($bole)->not->toBeNull();
    expect($bole->amharic_name)->toBe('ቦሌ');
    expect($bole->om_name)->toBe('Boolee');
    expect($bole->coordinates)->toBeArray();
    expect(count($bole->coordinates))->toBeGreaterThanOrEqual(4);
});

test('point-in-polygon ray casting algorithm accurately determines subcity containment', function () {
    $bole = SubcityBoundary::query()->where('name', 'Bole')->first();
    $yeka = SubcityBoundary::query()->where('name', 'Yeka')->first();

    // Coordinates of Bole Medhanialem (inside Bole)
    $bolePoint = [9.0016, 38.7844];
    expect($bole->containsPoint($bolePoint[0], $bolePoint[1]))->toBeTrue();
    expect($yeka->containsPoint($bolePoint[0], $bolePoint[1]))->toBeFalse();

    // Coordinates of Yeka Hillside (inside Yeka)
    $yekaPoint = [9.0350, 38.8150];
    expect($yeka->containsPoint($yekaPoint[0], $yekaPoint[1]))->toBeTrue();
    expect($bole->containsPoint($yekaPoint[0], $yekaPoint[1]))->toBeFalse();
});

test('ethiopian geo service resolves subcity boundary by coordinates', function () {
    $service = app(EthiopianGeoService::class);

    $boundary = $service->findSubcityByCoordinates(9.0016, 38.7844);
    expect($boundary)->not->toBeNull();
    expect($boundary->name)->toBe('Bole');
});

test('subcities boundaries endpoint returns valid geojson feature collection for leaflet maps', function () {
    $response = $this->getJson('/api/geo/subcities/boundaries');

    $response->assertOk()
        ->assertJsonStructure([
            'type',
            'features' => [
                '*' => [
                    'type',
                    'properties' => [
                        'id',
                        'city',
                        'name',
                        'amharic_name',
                        'om_name',
                        'center',
                        'properties_count',
                    ],
                    'geometry' => [
                        'type',
                        'coordinates',
                    ],
                ],
            ],
        ]);

    expect($response->json('type'))->toBe('FeatureCollection');
    expect(count($response->json('features')))->toBeGreaterThanOrEqual(11);
});

test('ethiopian date service performs exact bidirectional conversion and localization', function () {
    $dateService = app(EthiopianDateService::class);

    // 2026-09-11 is Meskerem 1, 2019 E.C.
    $eth = $dateService->toEthiopian('2026-09-11', 'am');
    expect($eth['year'])->toBe(2019);
    expect($eth['month'])->toBe(1);
    expect($eth['day'])->toBe(1);
    expect($eth['month_name'])->toBe('መስከረም');

    // Afan Oromo month name
    $ethOm = $dateService->toEthiopian('2026-09-11', 'om');
    expect($ethOm['month_name'])->toBe('Fulbaana');

    // Roundtrip back to Gregorian
    $greg = $dateService->toGregorian(2019, 1, 1);
    expect($greg->format('Y-m-d'))->toBe('2026-09-11');
});

test('property viewing scheduling works with date format submitted by ethiopian date picker', function () {
    $user = User::factory()->create([
        'name' => 'Abebe Desta',
        'email' => 'buyer.test@realestate.com',
        'role' => 'buyer',
    ]);
    $agentUser = User::factory()->create(['role' => 'agent']);
    $agent = Agent::create([
        'name' => 'Agent Broker',
        'email' => 'agent.test@realestate.com',
        'user_id' => $agentUser->id,
    ]);
    $property = Property::factory()->create([
        'agent_id' => $agent->id,
    ]);

    $targetDate = Carbon::now()->addDays(2)->format('Y-m-d');
    $response = $this->actingAs($user)->post("/properties/{$property->id}/viewings", [
        'buyer_name' => $user->name,
        'buyer_email' => $user->email,
        'buyer_phone' => '+251 91 122 3344',
        'viewing_type' => 'in_person',
        'viewing_date' => $targetDate,
        'viewing_time' => 'morning_09_11',
        'buyer_notes' => 'Visiting with family members',
    ]);

    $response->assertRedirect();

    $viewing = PropertyViewing::query()
        ->where('user_id', $user->id)
        ->where('property_id', $property->id)
        ->first();

    expect($viewing)->not->toBeNull();
    expect(Carbon::parse($viewing->viewing_date)->format('Y-m-d'))->toBe($targetDate);
});
