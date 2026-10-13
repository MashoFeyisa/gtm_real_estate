<?php

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\ListingPriceHistory;
use App\Models\Project;
use App\Models\ProjectUnit;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can create and update properties and listings', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/dashboard/properties', [
        'title' => 'Admin Villa Bole',
        'type' => 'sale',
        'category' => 'villa',
        'price' => 25000000,
        'bedrooms' => 5,
        'bathrooms' => 4,
        'area' => 350,
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'status' => 'published',
    ]);

    $response->assertRedirect();
    $property = Property::where('title', 'Admin Villa Bole')->first();
    expect($property)->not->toBeNull()
        ->and($property->price)->toEqual(25000000)
        ->and($property->status)->toBe('published')
        ->and($property->listings)->toHaveCount(1);
});

test('seller and property owner can create listing and toggle publish', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $owner = User::factory()->create(['role' => 'property_owner']);

    // Seller creates via /seller/listings
    $sellerResp = $this->actingAs($seller)->post('/seller/listings', [
        'title' => 'Seller Townhouse Kazanchis',
        'type' => 'sale',
        'category' => 'home',
        'price' => 12000000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 180,
        'city' => 'Addis Ababa',
        'subcity' => 'Kirkos',
        'status' => 'published',
    ]);

    $sellerResp->assertRedirect(route('seller.dashboard'));
    $sellerProp = Property::where('title', 'Seller Townhouse Kazanchis')->first();
    expect($sellerProp)->not->toBeNull()
        ->and($sellerProp->user_id)->toBe($seller->id)
        ->and($sellerProp->is_active)->toBeTrue();

    // Owner creates via /dashboard/properties
    $ownerResp = $this->actingAs($owner)->post('/dashboard/properties', [
        'title' => 'Owner Apartment Bole Atlas',
        'type' => 'sale',
        'category' => 'apartment',
        'price' => 8500000,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 110,
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'status' => 'published',
    ]);

    $ownerResp->assertRedirect(route('seller.dashboard'));
    $ownerProp = Property::where('title', 'Owner Apartment Bole Atlas')->first();
    expect($ownerProp)->not->toBeNull()
        ->and($ownerProp->user_id)->toBe($owner->id);

    // Toggle publish
    $toggleResp = $this->actingAs($owner)->post('/seller/listings/'.$ownerProp->id.'/publish');
    $toggleResp->assertRedirect(route('seller.dashboard'));
    $ownerProp->refresh();
    expect($ownerProp->is_active)->toBeFalse()
        ->and($ownerProp->status)->toBe('draft');
});

test('landlord can create property listings', function () {
    $landlord = User::factory()->create(['role' => 'landlord']);

    $response = $this->actingAs($landlord)->post('/seller/listings', [
        'title' => 'Landlord Rental Studio CMC',
        'type' => 'rent',
        'category' => 'apartment',
        'price' => 35000,
        'bedrooms' => 1,
        'bathrooms' => 1,
        'area' => 55,
        'city' => 'Addis Ababa',
        'subcity' => 'Yeka',
        'status' => 'published',
    ]);

    $response->assertRedirect(route('seller.dashboard'));
    $prop = Property::where('title', 'Landlord Rental Studio CMC')->first();
    expect($prop)->not->toBeNull()
        ->and($prop->user_id)->toBe($landlord->id)
        ->and($prop->type)->toBe('rent');
});

test('host can create short stay listing via host portal', function () {
    $host = User::factory()->create(['role' => 'host']);

    $response = $this->actingAs($host)->post('/host/stays', [
        'title' => 'Bole Airport Luxury Guest Suite',
        'price' => 4500, // Nightly ETB
        'cleaning_fee' => 500,
        'bedrooms' => 1,
        'bathrooms' => 1,
        'check_in_time' => '14:00',
        'check_out_time' => '11:00',
        'house_rules' => 'No pets. Quiet hours after 22:00.',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'status' => 'published',
    ]);

    $response->assertRedirect(route('host.dashboard'));
    $prop = Property::where('title', 'Bole Airport Luxury Guest Suite')->first();
    expect($prop)->not->toBeNull()
        ->and($prop->host_id)->toBe($host->id)
        ->and($prop->type)->toBe('stay')
        ->and($prop->price)->toEqual(4500)
        ->and($prop->cleaning_fee)->toEqual(500);

    // Host can pause stay
    $this->actingAs($host)->post('/host/stays/'.$prop->id.'/publish');
    $prop->refresh();
    expect($prop->is_active)->toBeFalse();
});

test('developer can publish project unit to marketplace listing', function () {
    $developer = User::factory()->create(['role' => 'developer']);

    $project = Project::create([
        'name' => 'Entoto Panorama Residences',
        'slug' => 'entoto-panorama',
        'developer_name' => 'Entoto Real Estate PLC',
        'project_type' => 'residential_tower',
        'city' => 'Addis Ababa',
        'subcity' => 'Gulele',
        'address' => 'Entoto Park Road',
        'starting_price' => 6000000,
        'status' => 'published',
    ]);

    $unit = ProjectUnit::create([
        'project_id' => $project->id,
        'unit_number' => 'EP-402',
        'floor_number' => '4th Floor',
        'unit_type' => '2_bedroom',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area_sqm' => 95,
        'price' => 7500000,
        'currency' => 'ETB',
        'status' => 'available',
    ]);

    $response = $this->actingAs($developer)->post('/developments/units/'.$unit->id.'/publish-listing');
    $response->assertRedirect();

    $publishedProp = Property::where('title', 'like', '%EP-402%')->first();
    expect($publishedProp)->not->toBeNull()
        ->and($publishedProp->price)->toEqual(7500000)
        ->and($publishedProp->is_active)->toBeTrue()
        ->and($publishedProp->user_id)->toBe($developer->id);
});

test('unauthorized roles cannot create properties or listings', function () {
    $unauthorizedRoles = ['buyer', 'tenant', 'cleaner', 'service_provider'];

    foreach ($unauthorizedRoles as $role) {
        $user = User::factory()->create(['role' => $role]);

        $resp1 = $this->actingAs($user)->post('/dashboard/properties', [
            'title' => 'Unauthorized Property Attempt',
            'type' => 'sale',
            'category' => 'villa',
            'price' => 5000000,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area' => 150,
            'status' => 'published',
        ]);
        $resp1->assertForbidden();

        $resp2 = $this->actingAs($user)->post('/seller/listings', [
            'title' => 'Unauthorized Seller Attempt',
            'type' => 'sale',
            'category' => 'home',
            'price' => 5000000,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area' => 150,
            'status' => 'published',
        ]);
        $resp2->assertForbidden();
    }
});

test('guest cannot create properties or listings without authentication', function () {
    $response = $this->post('/dashboard/properties', [
        'title' => 'Guest Property',
        'price' => 1000000,
    ]);
    $response->assertRedirect(route('login'));
});

test('owner cannot update another users property unless authorized', function () {
    $ownerA = User::factory()->create(['role' => 'property_owner']);
    $ownerB = User::factory()->create(['role' => 'property_owner']);

    $propA = Property::create([
        'user_id' => $ownerA->id,
        'title' => 'Owner A Private Villa',
        'slug' => 'owner-a-villa',
        'price' => 10000000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 200,
        'type' => 'sale',
        'status' => 'published',
    ]);

    // Owner B tries to update Owner A's property
    $response = $this->actingAs($ownerB)->put('/dashboard/properties/'.$propA->id, [
        'title' => 'Hacked Villa Title',
        'price' => 1000,
        'bedrooms' => 1,
        'bathrooms' => 1,
        'area' => 50,
        'status' => 'published',
        'type' => 'sale',
    ]);

    $response->assertForbidden();
    $propA->refresh();
    expect($propA->title)->toBe('Owner A Private Villa');
});

test('price update records to ListingPriceHistory and AuditLog', function () {
    $owner = User::factory()->create(['role' => 'property_owner']);

    $property = Property::create([
        'user_id' => $owner->id,
        'title' => 'Bole Dynamic Priced Villa',
        'slug' => 'bole-dynamic-priced-villa',
        'price' => 20000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 250,
        'type' => 'sale',
        'status' => 'published',
    ]);

    $listing = $property->listings()->first();
    expect($listing)->not->toBeNull();

    // Owner updates price
    $this->actingAs($owner)->put('/dashboard/properties/'.$property->id, [
        'title' => 'Bole Dynamic Priced Villa',
        'price' => 22000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 250,
        'type' => 'sale',
        'status' => 'published',
    ]);

    $listing->refresh();
    expect($listing->price)->toEqual(22000000);

    $priceHistory = ListingPriceHistory::where('listing_id', $listing->id)->first();
    expect($priceHistory)->not->toBeNull()
        ->and($priceHistory->old_price)->toEqual(20000000)
        ->and($priceHistory->new_price)->toEqual(22000000);

    $auditLog = AuditLog::where('action', 'listing.price_updated')
        ->where('entity_id', $listing->id)
        ->first();
    expect($auditLog)->not->toBeNull();
});

test('stays send-arrival-instructions command dispatches notifications on check-in day', function () {
    $guest = User::factory()->create(['role' => 'traveler', 'name' => 'Almaz Guest']);
    $host = User::factory()->create(['role' => 'host']);

    $property = Property::create([
        'host_id' => $host->id,
        'title' => 'Kazanchis Business Stay',
        'slug' => 'kazanchis-biz-stay',
        'price' => 3000,
        'check_in_time' => '15:00',
        'house_rules' => 'No parties.',
        'type' => 'stay',
        'status' => 'published',
    ]);

    $booking = Booking::create([
        'user_id' => $guest->id,
        'property_id' => $property->id,
        'guest_name' => 'Almaz Guest',
        'guest_email' => $guest->email,
        'guest_phone' => '0911223344',
        'check_in_date' => now()->toDateString(),
        'check_out_date' => now()->addDays(3)->toDateString(),
        'total_nights' => 3,
        'nightly_price' => 3000,
        'accommodation_total' => 9000,
        'total_amount' => 9000,
        'status' => 'confirmed',
    ]);

    Artisan::call('stays:send-arrival-instructions');

    expect($guest->notifications()->count())->toBe(1);
    $notification = $guest->notifications()->first();
    expect($notification->data['type'])->toBe('guest_booking')
        ->and($notification->data['message'])->toContain('Kazanchis Business Stay');

    $auditLog = AuditLog::where('action', 'stays.arrival_instructions_sent')
        ->where('entity_id', $booking->id)
        ->first();
    expect($auditLog)->not->toBeNull();
});
