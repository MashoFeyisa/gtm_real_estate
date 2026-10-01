<?php

use App\Models\Property;

test('homepage displays safety and direct deal warning message with all required guidelines and disclaimer', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Safety & Direct Deal')
        ->assertSee('Only inspect properties accompanied by the verified agent.')
        ->assertSee('Never send upfront cash or advance before contract signing.')
        ->assertSee('Branded agreements are generated automatically on GTM Real Estate.')
        ->assertSee('Any client who buys without an agreement given in this webapp, the company does not take responsibility.')
        ->assertSeeInOrder(['id="safety-advisory"', 'Buyer Protection & Safety Advisory', '<footer', 'Company', 'Properties', 'Contact us'], false);
});

test('app homepage route also displays safety and direct deal warning message', function () {
    $response = $this->get('/app');

    $response->assertOk()
        ->assertSee('Safety & Direct Deal')
        ->assertSee('Only inspect properties accompanied by the verified agent.')
        ->assertSee('Never send upfront cash or advance before contract signing.')
        ->assertSee('Branded agreements are generated automatically on GTM Real Estate.')
        ->assertSee('Any client who buys without an agreement given in this webapp, the company does not take responsibility.');
});

test('property show page displays safety and direct deal warning with company disclaimer', function () {
    $property = Property::query()->create([
        'title' => 'Sample Safety Villa',
        'slug' => 'sample-safety-villa',
        'price' => 12000000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 200,
        'type' => 'sale',
        'status' => 'available',
        'city' => 'Harar',
        'is_active' => true,
    ]);

    $response = $this->get('/properties/'.$property->slug);

    $response->assertOk()
        ->assertSee('Safety & Direct Deal')
        ->assertSee('Only inspect properties accompanied by the verified agent.')
        ->assertSee('Never send upfront cash or advance before contract signing.')
        ->assertSee('Branded agreements are generated automatically on GTM Real Estate.')
        ->assertSee('Any client who buys without an agreement given in this webapp, the company does not take responsibility.');
});
