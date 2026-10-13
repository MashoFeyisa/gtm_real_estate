<?php

use App\Models\Agent;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Str;

function createSearchProperty(array $attributes = []): Property
{
    $title = $attributes['title'] ?? 'Test Property';

    return Property::create(array_merge([
        'title' => $title,
        'slug' => Str::slug($title).'-'.uniqid(),
        'price' => 5000000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 120,
        'type' => 'sale',
        'property_category' => 'home',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
    ], $attributes));
}

test('property search can filter by requested cities', function (string $cityName, string $propertyCity) {
    createSearchProperty([
        'title' => "Special Listing in {$cityName}",
        'city' => $propertyCity,
    ]);

    $response = $this->get(route('properties.index', ['city' => $cityName]));

    $response->assertOk();
    $response->assertSee("Special Listing in {$cityName}");
})->with([
    ['Addis Ababa', 'Addis Ababa'],
    ['Harar', 'ethiopia Harar'],
    ['Jijjiga', 'Jijjiga'],
    ['Shashamane', 'shashamane'],
    ['Dire Dawa', 'Dire Dawa'],
    ['Adama', 'Adama'],
    ['Hawassa', 'Hawassa'],
]);

test('properties can be filtered by condominium, guest house, and hotel categories', function () {
    $condo = createSearchProperty([
        'title' => 'Luxury Bole Condominium',
        'property_category' => 'condominium',
    ]);

    $guestHouse = createSearchProperty([
        'title' => 'Serene Sarbet Guest House',
        'property_category' => 'guest_house',
    ]);

    $hotel = createSearchProperty([
        'title' => 'Kazanchis Boutique Hotel',
        'property_category' => 'hotel',
    ]);

    $this->get(route('properties.condominiums'))
        ->assertOk()
        ->assertSee('Luxury Bole Condominium')
        ->assertDontSee('Serene Sarbet Guest House');

    $this->get(route('properties.guest_houses'))
        ->assertOk()
        ->assertSee('Serene Sarbet Guest House')
        ->assertDontSee('Luxury Bole Condominium');

    $this->get(route('properties.hotels'))
        ->assertOk()
        ->assertSee('Kazanchis Boutique Hotel')
        ->assertDontSee('Serene Sarbet Guest House');
});

test('admin can create property with condominium, guest house, and hotel categories', function (string $category) {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('dashboard.properties.store'), [
        'title' => "New {$category} Listing",
        'price' => 4500000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 85,
        'type' => 'rent',
        'category' => $category,
        'status' => 'published',
        'city' => 'Addis Ababa',
    ]);

    $response->assertRedirect(route('dashboard'));

    $property = Property::where('title', "New {$category} Listing")->first();
    expect($property)->not->toBeNull()
        ->and($property->property_category)->toBe($category);
})->with([
    'condominium',
    'guest_house',
    'hotel',
]);

test('agent can create property with short stay and condominium categories', function (string $category) {
    $user = User::factory()->create(['role' => 'agent']);
    $agent = Agent::create([
        'name' => 'Agent Tester',
        'email' => 'agent.test@example.com',
        'phone' => '+251911223344',
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->post(route('agent.properties.store'), [
        'title' => "Agent {$category} Listing",
        'price' => 35000,
        'bedrooms' => 1,
        'bathrooms' => 1,
        'area' => 60,
        'type' => 'rent',
        'category' => $category,
        'status' => 'published',
        'city' => 'Hawassa',
    ]);

    $response->assertRedirect(route('agent.portal', ['section' => 'listings']));

    $property = Property::where('title', "Agent {$category} Listing")->first();
    expect($property)->not->toBeNull()
        ->and($property->property_category)->toBe($category);
})->with([
    'condominium',
    'guest_house',
    'hotel',
]);
