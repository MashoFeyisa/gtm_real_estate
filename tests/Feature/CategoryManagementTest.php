<?php

use App\Models\Agent;
use App\Models\Listing;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\User;
use Database\Seeders\PropertyTaxonomySeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(PropertyTaxonomySeeder::class);

    $this->admin = User::create([
        'name' => 'Category Admin',
        'email' => 'category.admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->agent = User::create([
        'name' => 'Category Agent',
        'email' => 'category.agent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);
});

test('taxonomy seeder loads the specification categories listing types and modes', function () {
    expect(PropertyCategory::query()->count())->toBe(33);
    expect(ListingType::query()->count())->toBe(11);

    expect(PropertyCategory::query()->where('group', 'Residential')->count())->toBe(10);
    expect(ListingType::MODES)->toHaveCount(5);

    expect(PropertyCategory::query()->where('slug', 'penthouse')->exists())->toBeTrue();
    expect(PropertyCategory::query()->where('slug', 'development-project')->exists())->toBeTrue();
    expect(ListingType::query()->where('slug', 'for-lease')->value('mode'))->toBe('lease');
    expect(ListingType::query()->where('slug', 'off-plan')->value('mode'))->toBe('new_projects');
    expect(ListingType::query()->where('slug', 'short-stay')->value('mode'))->toBe('stay');
});

test('admin dashboard renders the category management desk', function () {
    $response = $this->actingAs($this->admin)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Category Management');
    $response->assertSee('Penthouse');
    $response->assertSee('Investment Opportunity');
    $response->assertSee('Transaction mode');
});

test('admin can create a property category', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.categories.store'), [
            'group' => 'Residential',
            'name' => 'Beach House',
            'icon' => '🏖️',
            'sort_order' => 99,
            'is_active' => '1',
        ])
        ->assertRedirect();

    $category = PropertyCategory::query()->where('slug', 'beach-house')->first();

    expect($category)->not()->toBeNull();
    expect($category->group)->toBe('Residential');
    expect($category->is_active)->toBeTrue();
});

test('admin can update a property category including its group and active flag', function () {
    $category = PropertyCategory::query()->where('slug', 'penthouse')->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('admin.categories.update', $category), [
            'group' => 'Hospitality',
            'name' => 'Sky Penthouse',
            'sort_order' => 3,
        ])
        ->assertRedirect();

    $category->refresh();

    expect($category->name)->toBe('Sky Penthouse');
    expect($category->group)->toBe('Hospitality');
    expect($category->sort_order)->toBe(3);
    expect($category->is_active)->toBeFalse();
});

test('admin can delete an unused property category but not one used by properties', function () {
    $unused = PropertyCategory::query()->where('slug', 'penthouse')->firstOrFail();

    $this->actingAs($this->admin)
        ->delete(route('admin.categories.destroy', $unused))
        ->assertRedirect();

    expect(PropertyCategory::query()->where('slug', 'penthouse')->exists())->toBeFalse();

    $agent = Agent::create([
        'name' => 'Category Brokerage',
        'email' => 'category.brokerage@example.com',
        'phone' => '+251 91 555 0000',
    ]);

    Property::create([
        'title' => 'Penthouse for deletion test',
        'slug' => 'penthouse-for-deletion-test',
        'price' => 9000000,
        'type' => 'sale',
        'property_category' => 'duplex',
        'status' => 'available',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $inUse = PropertyCategory::query()->where('slug', 'duplex')->firstOrFail();

    $this->actingAs($this->admin)
        ->from(route('dashboard'))
        ->delete(route('admin.categories.destroy', $inUse))
        ->assertRedirect();

    expect(PropertyCategory::query()->where('slug', 'duplex')->exists())->toBeTrue();
});

test('category creation validates the group', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Missing Group Category',
        ])
        ->assertSessionHasErrors('group');

    expect(PropertyCategory::query()->where('name', 'Missing Group Category')->exists())->toBeFalse();
});

test('non admins cannot manage categories', function () {
    $this->actingAs($this->agent)
        ->post(route('admin.categories.store'), [
            'group' => 'Residential',
            'name' => 'Sneaky Category',
        ])
        ->assertForbidden();

    expect(PropertyCategory::query()->where('slug', 'sneaky-category')->exists())->toBeFalse();
});

test('admin can create configure and delete listing types', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.listing-types.store'), [
            'mode' => 'new_projects',
            'name' => 'Construction-Linked Offer',
            'sort_order' => 4,
            'is_active' => '1',
        ])
        ->assertRedirect();

    $listingType = ListingType::query()->where('slug', 'construction-linked-offer')->firstOrFail();

    expect($listingType->mode)->toBe('new_projects');

    $this->actingAs($this->admin)
        ->put(route('admin.listing-types.update', $listingType), [
            'mode' => 'buy',
            'name' => 'Construction-Linked Offer',
        ])
        ->assertRedirect();

    expect($listingType->fresh()->mode)->toBe('buy');

    $this->actingAs($this->admin)
        ->from(route('dashboard'))
        ->delete(route('admin.listing-types.destroy', $listingType))
        ->assertRedirect();

    expect(ListingType::query()->where('slug', 'construction-linked-offer')->exists())->toBeFalse();
});

test('listing types used by listings cannot be deleted', function () {
    $agent = Agent::create([
        'name' => 'Listing Brokerage',
        'email' => 'listing.brokerage@example.com',
        'phone' => '+251 91 555 1111',
    ]);

    $property = Property::create([
        'title' => 'Typed listing property',
        'slug' => 'typed-listing-property',
        'price' => 8000000,
        'type' => 'sale',
        'status' => 'available',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $listing = Listing::create([
        'property_id' => $property->id,
        'listing_type' => 'for-sale',
        'title' => 'Typed listing',
        'price' => 8000000,
        'status' => 'published',
    ]);

    $listingType = ListingType::query()->where('slug', 'for-sale')->firstOrFail();

    $this->actingAs($this->admin)
        ->from(route('dashboard'))
        ->delete(route('admin.listing-types.destroy', $listingType))
        ->assertRedirect();

    expect(ListingType::query()->where('slug', 'for-sale')->exists())->toBeTrue();
});

test('property creation form reads categories from the database', function () {
    PropertyCategory::query()->create([
        'group' => 'Special',
        'name' => 'Campus Land',
        'slug' => 'campus-land',
        'is_active' => true,
        'sort_order' => 500,
    ]);

    $response = $this->actingAs($this->admin)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Campus Land');
});
