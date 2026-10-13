<?php

use App\Models\Property;

test('homepage property cards do not render compare controls', function () {
    Property::create([
        'title' => 'Homepage Listing Villa',
        'slug' => 'homepage-listing-villa',
        'price' => 25000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 300,
        'city' => 'Addis Ababa',
        'type' => 'sale',
        'featured' => true,
        'status' => 'available',
        'is_active' => true,
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertDontSee('compare-toggle-form', false);
    $response->assertDontSee('compare-matrix-marker-badge', false);
});

test('properties listing renders compare controls for marking a property', function () {
    $property = Property::create([
        'title' => 'Markable Listing Villa',
        'slug' => 'markable-listing-villa',
        'price' => 30000000,
        'bedrooms' => 5,
        'bathrooms' => 4,
        'area' => 350,
        'city' => 'Addis Ababa',
        'type' => 'sale',
        'status' => 'available',
        'is_active' => true,
    ]);

    $response = $this->get(route('properties.index'));

    $response->assertOk();
    $response->assertSee(route('properties.compare.toggle', $property), false);
    $response->assertSee('compare-toggle-form', false);
    $response->assertSee('compare-matrix-marker-badge', false);
});
