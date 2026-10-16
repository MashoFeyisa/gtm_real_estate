<?php

use App\Models\Lease;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * A user who manages a property without owning the record must still be able
 * to upload media, matching the properties listed on the seller dashboard.
 */
function propertyManager(string $role): User
{
    return User::factory()->create(['role' => $role]);
}

it('lets a document contributor upload gallery photos on an orphaned property', function () {
    Storage::fake('public');

    $manager = propertyManager('property_owner');
    $property = Property::factory()->create(['user_id' => null, 'host_id' => null]);

    // The property only appears on the dashboard because of this contribution.
    PropertyDocument::factory()->create(['property_id' => $property->id, 'user_id' => $manager->id]);

    expect($property->fresh()->isManagedBy($manager))->toBeTrue();

    $response = $this->actingAs($manager)->post(
        route('seller.photos.upload', $property),
        ['photos' => [UploadedFile::fake()->image('villa.jpg'), UploadedFile::fake()->image('garden.jpg')]],
    );

    $response->assertRedirect(route('seller.dashboard'));
    $response->assertSessionHas('success');
    expect($property->fresh()->images()->count())->toBe(2);
});

it('lets a lease landlord set a cover photo on an orphaned property', function () {
    $manager = propertyManager('landlord');
    $property = Property::factory()->create(['user_id' => null, 'host_id' => null]);

    Lease::factory()->create([
        'property_id' => $property->id,
        'landlord_user_id' => $manager->id,
    ]);

    $image = PropertyImage::factory()->create(['property_id' => $property->id, 'image_path' => 'properties/gallery/cover.jpg']);

    $response = $this->actingAs($manager)->post(route('seller.photos.cover', $image));

    $response->assertRedirect(route('seller.dashboard'));
    expect($property->fresh()->image_path)->toBe('properties/gallery/cover.jpg');
});

it('lets a property owner delete a photo they manage but do not own', function () {
    Storage::fake('public');
    Storage::disk('public')->put('properties/gallery/old.jpg', 'contents');

    $manager = propertyManager('property_owner');
    $property = Property::factory()->create(['user_id' => null, 'host_id' => null]);
    PropertyDocument::factory()->create(['property_id' => $property->id, 'user_id' => $manager->id]);

    $image = PropertyImage::factory()->create(['property_id' => $property->id, 'image_path' => 'properties/gallery/old.jpg']);

    $response = $this->actingAs($manager)->delete(route('seller.photos.destroy', $image));

    $response->assertRedirect(route('seller.dashboard'));
    expect($property->fresh()->images()->count())->toBe(0);
});

it('still blocks unrelated users from uploading photos', function () {
    Storage::fake('public');

    $stranger = propertyManager('buyer');
    $property = Property::factory()->create(['user_id' => null, 'host_id' => null]);
    PropertyDocument::factory()->create(['property_id' => $property->id, 'user_id' => User::factory()->create()->id]);

    $response = $this->actingAs($stranger)->post(
        route('seller.photos.upload', $property),
        ['photos' => [UploadedFile::fake()->image('sneaky.jpg')]],
    );

    $response->assertForbidden();
    expect($property->fresh()->images()->count())->toBe(0);
});

it('blocks users with no connection to the property entirely', function () {
    $stranger = propertyManager('buyer');
    $property = Property::factory()->create(['user_id' => null, 'host_id' => null]);

    $this->actingAs($stranger)
        ->post(route('seller.photos.upload', $property), ['photos' => [UploadedFile::fake()->image('x.jpg')]])
        ->assertForbidden();
});

it('keeps deletion of the property record limited to its owner', function () {
    $manager = propertyManager('property_owner');
    $owner = propertyManager('property_owner');
    $property = Property::factory()->create(['user_id' => $owner->id]);

    PropertyDocument::factory()->create([
        'property_id' => $property->id,
        'user_id' => $manager->id,
    ]);

    expect(Gate::forUser($manager)->denies('delete', $property))->toBeTrue();
    expect(Gate::forUser($owner)->allows('delete', $property))->toBeTrue();
});
