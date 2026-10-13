<?php

use App\Models\Agent;
use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\PropertyTaxonomySeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(PropertyTaxonomySeeder::class);

    $this->companyUser = User::create([
        'name' => 'Company Manager',
        'email' => 'company.listings@example.com',
        'password' => Hash::make('password123'),
        'role' => 'company',
    ]);

    $this->agentUser = User::create([
        'name' => 'Listing Agent',
        'email' => 'listing.agent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Listing Agent Brokerage',
        'email' => 'listing.agent.brokerage@example.com',
    ]);

    $this->buyer = User::create([
        'name' => 'Listing Buyer',
        'email' => 'listing.buyer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->property = Property::create([
        'title' => 'Company Managed Villa',
        'slug' => 'company-managed-villa',
        'description' => 'A managed villa with three bedrooms, a garden, and covered parking in a secure compound.',
        'price' => 9600000,
        'type' => 'sale',
        'property_category' => 'villa',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'subcity' => 'CMC',
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 210,
    ]);
});

test('company dashboard renders the listing studio with management actions', function () {
    $response = $this->actingAs($this->companyUser)->get(route('company.dashboard'));

    $response->assertOk();
    $response->assertSee('Company Property Studio');
    $response->assertSee('+ New Listing / Property');
    $response->assertSee('Company Managed Villa');
    $response->assertSee('Publish');
    $response->assertSee('Unpublish');
    $response->assertSee('Available');
    $response->assertSee('Rented');
    $response->assertSee('Sold');
    $response->assertSee('Delete');

    // The sidebar no longer carries the "Core Pillars" wording
    $response->assertDontSee('Core Pillars');
    $response->assertSee('Marketplace Desks');
});

test('company listings desk shows per listing management actions', function () {
    $response = $this->actingAs($this->companyUser)->get(route('company.dashboard'));

    $response->assertOk();
    $response->assertSee('Company Listings — Manage');
    $response->assertSee(route('company.listings.publish', $this->property));
});

test('company can add a listing', function () {
    $this->actingAs($this->companyUser)
        ->post(route('company.listings.store'), [
            'title' => 'Ayat Garden Apartment',
            'type' => 'rent',
            'category' => 'apartment',
            'price' => 25000,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'area' => 95,
            'city' => 'Addis Ababa',
            'subcity' => 'Ayat',
            'status' => 'published',
        ])
        ->assertRedirect();

    $property = Property::query()->where('title', 'Ayat Garden Apartment')->first();

    expect($property)->not()->toBeNull();
    expect($property->type)->toBe('rent');
    expect($property->property_category)->toBe('apartment');
    expect($property->is_active)->toBeTrue();

    // The property saved hook creates the marketplace listing
    expect(Listing::query()->where('property_id', $property->id)->exists())->toBeTrue();
});

test('company listing validation rejects incomplete submissions', function () {
    $this->actingAs($this->companyUser)
        ->post(route('company.listings.store'), [
            'type' => 'sale',
            'price' => 1000,
        ])
        ->assertSessionHasErrors(['title', 'bedrooms', 'bathrooms', 'area', 'status']);

    expect(Property::query()->count())->toBe(1);
});

test('company can edit a listing', function () {
    $this->actingAs($this->companyUser)
        ->get(route('company.listings.edit-form', $this->property))
        ->assertRedirect();

    $this->actingAs($this->companyUser)
        ->put(route('company.listings.update', $this->property), [
            'title' => 'Company Managed Villa (Renovated)',
            'type' => 'sale',
            'category' => 'villa',
            'price' => 10100000,
            'bedrooms' => 3,
            'bathrooms' => 3,
            'area' => 215,
            'city' => 'Addis Ababa',
            'subcity' => 'CMC',
            'status' => 'published',
        ])
        ->assertRedirect();

    $property = $this->property->fresh();

    expect($property->title)->toBe('Company Managed Villa (Renovated)');
    expect((float) $property->price)->toBe(10100000.0);
    expect($property->bathrooms)->toBe(3);
});

test('company can publish and unpublish a listing', function () {
    $this->actingAs($this->companyUser)
        ->post(route('company.listings.publish', $this->property))
        ->assertRedirect();

    $property = $this->property->fresh();

    expect($property->is_active)->toBeFalse();
    expect($property->status)->toBe('archived');

    $this->actingAs($this->companyUser)
        ->post(route('company.listings.publish', $property->fresh()))
        ->assertRedirect();

    $property = $this->property->fresh();

    expect($property->is_active)->toBeTrue();
    expect($property->status)->toBe('published');
});

test('company can mark a listing available rented or sold', function () {
    foreach ([['rented', false], ['sold', false], ['available', true]] as [$status, $isActive]) {
        $this->actingAs($this->companyUser)
            ->post(route('company.listings.status', ['property' => $this->property, 'status' => $status]))
            ->assertRedirect();

        $property = $this->property->fresh();

        expect($property->status)->toBe($status);
        expect($property->is_active)->toBe($isActive);
    }
});

test('company can delete a listing', function () {
    $this->actingAs($this->companyUser)
        ->from(route('company.dashboard'))
        ->delete(route('company.listings.destroy', $this->property))
        ->assertRedirect();

    expect(Property::query()->whereKey($this->property->id)->exists())->toBeFalse();
});

test('non company roles cannot manage listings', function () {
    $this->actingAs($this->agentUser)
        ->post(route('company.listings.store'), [
            'title' => 'Sneaky Listing',
            'type' => 'sale',
            'price' => 500000,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'area' => 50,
            'status' => 'published',
        ])
        ->assertForbidden();

    $this->actingAs($this->buyer)
        ->post(route('company.listings.publish', $this->property))
        ->assertForbidden();

    $this->actingAs($this->buyer)
        ->post(route('company.listings.status', ['property' => $this->property, 'status' => 'sold']))
        ->assertForbidden();

    $this->actingAs($this->buyer)
        ->delete(route('company.listings.destroy', $this->property))
        ->assertForbidden();

    expect(Property::query()->where('title', 'Sneaky Listing')->exists())->toBeFalse();
    expect($this->property->fresh()->is_active)->toBeTrue();
});

test('admins keep full company listing management access', function () {
    $admin = User::create([
        'name' => 'Listing Admin',
        'email' => 'listing.admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post(route('company.listings.status', ['property' => $this->property, 'status' => 'sold']))
        ->assertRedirect();

    expect($this->property->fresh()->status)->toBe('sold');
});
