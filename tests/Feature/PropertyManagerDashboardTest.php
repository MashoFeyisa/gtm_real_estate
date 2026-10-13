<?php

use App\Models\Lease;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('property manager can view control center dashboard with unit management modal', function () {
    $pm = User::factory()->create([
        'role' => 'property_manager',
    ]);

    $property = Property::factory()->create([
        'title' => 'Sample Managed Residence',
        'city' => 'Addis Ababa',
        'price' => 50000,
    ]);

    $response = $this->actingAs($pm)->get(route('property-manager.dashboard'));

    $response->assertOk();
    $response->assertSee('Property Manager Control Center');
    $response->assertSee('Units & Occupancy Roll');
    $response->assertSee('Add New Unit');
    $response->assertSee('add-unit-modal');
    $response->assertSee('Sample Managed Residence');
    $response->assertSee('Manage / Edit');
});

test('property manager can register a new unit with image to portfolio', function () {
    Storage::fake('public');

    $pm = User::factory()->create([
        'role' => 'property_manager',
    ]);

    $image = UploadedFile::fake()->image('unit-luxury.jpg');

    $response = $this->actingAs($pm)->post(route('property_manager.units.store'), [
        'title' => 'Skyline Tower Unit 702',
        'property_category' => 'apartment',
        'type' => 'rent',
        'price' => 65000,
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 120,
        'status' => 'available',
        'image' => $image,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('properties', [
        'title' => 'Skyline Tower Unit 702',
        'property_category' => 'apartment',
        'type' => 'rent',
        'price' => 65000,
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
    ]);

    $createdProperty = Property::where('title', 'Skyline Tower Unit 702')->first();
    expect($createdProperty)->not->toBeNull();
    expect($createdProperty->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($createdProperty->image_path);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'property_manager.unit_created',
        'entity_type' => 'Property',
        'entity_id' => $createdProperty->id,
        'user_id' => $pm->id,
    ]);
});

test('property manager can update an existing unit status and details', function () {
    Storage::fake('public');

    $pm = User::factory()->create([
        'role' => 'property_manager',
    ]);

    $property = Property::factory()->create([
        'title' => 'Existing Managed Unit',
        'price' => 30000,
        'status' => 'available',
    ]);

    $image = UploadedFile::fake()->image('unit-updated.jpg');

    $response = $this->actingAs($pm)->put(route('property_manager.units.update', $property->id), [
        'title' => 'Existing Managed Unit - Renovation Complete',
        'price' => 42000,
        'status' => 'rented',
        'property_category' => 'condominium',
        'type' => 'rent',
        'city' => 'Addis Ababa',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 90,
        'image' => $image,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $property->refresh();
    expect($property->title)->toBe('Existing Managed Unit - Renovation Complete');
    expect((float) $property->price)->toBe(42000.0);
    expect($property->status)->toBe('rented');
    expect($property->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($property->image_path);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'property_manager.unit_updated',
        'entity_type' => 'Property',
        'entity_id' => $property->id,
        'user_id' => $pm->id,
    ]);
});

test('property manager can download tenancy agreement document', function () {
    Storage::fake('local');

    $pm = User::factory()->create([
        'role' => 'property_manager',
    ]);

    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create();

    $lease = Lease::factory()->create([
        'property_id' => $property->id,
        'tenant_user_id' => $tenant->id,
        'tenant_name' => 'Abebe Kebede',
        'monthly_rent' => 25000,
        'status' => 'active',
    ]);

    $response = $this->actingAs($pm)->get(route('leases.agreement', $lease->id));

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('rental-agreement.pdf');
});

test('property manager dashboard displays lease terms modal for active tenants', function () {
    $pm = User::factory()->create([
        'role' => 'property_manager',
    ]);

    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create(['title' => 'Grand Bole Apartment 4B']);

    $lease = Lease::factory()->create([
        'property_id' => $property->id,
        'tenant_user_id' => $tenant->id,
        'tenant_name' => 'Almaz Wolde',
        'monthly_rent' => 35000,
        'security_deposit' => 70000,
        'status' => 'active',
    ]);

    $response = $this->actingAs($pm)->get(route('property-manager.dashboard'));

    $response->assertOk();
    $response->assertSee('Almaz Wolde');
    $response->assertSee('Lease Terms');
    $response->assertSee('lease-modal-'.$lease->id);
    $response->assertSee('Tenancy Terms & Agreement');
    $response->assertSee(number_format(70000));
    $response->assertSee(route('leases.agreement', $lease->id));
});

test('property manager can dispatch a cleaning turnover task', function () {
    $pm = User::factory()->create([
        'role' => 'property_manager',
    ]);

    $cleaner = User::factory()->create([
        'role' => 'cleaner',
    ]);

    $property = Property::factory()->create([
        'title' => 'Cleaned Unit 101',
    ]);

    $response = $this->actingAs($pm)->post(route('property_manager.cleaning.dispatch'), [
        'property_id' => $property->id,
        'cleaner_user_id' => $cleaner->id,
        'room_number' => 'Unit 101',
        'priority' => 'urgent',
        'scheduled_date' => now()->toDateString(),
        'notes' => 'Prepare for guest arrival at 2pm',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('cleaning_tasks', [
        'property_id' => $property->id,
        'cleaner_user_id' => $cleaner->id,
        'room_number' => 'Unit 101',
        'priority' => 'urgent',
    ]);
});

test('regular client cannot access property manager unit creation or update endpoints', function () {
    $client = User::factory()->create([
        'role' => 'buyer',
    ]);

    $property = Property::factory()->create();

    $storeResponse = $this->actingAs($client)->post(route('property_manager.units.store'), [
        'title' => 'Unauthorized Unit',
        'property_category' => 'apartment',
        'type' => 'rent',
        'price' => 20000,
        'city' => 'Addis Ababa',
        'status' => 'available',
    ]);

    $storeResponse->assertForbidden();

    $updateResponse = $this->actingAs($client)->put(route('property_manager.units.update', $property->id), [
        'title' => 'Unauthorized Unit Update',
        'price' => 25000,
        'status' => 'available',
    ]);

    $updateResponse->assertForbidden();
});
