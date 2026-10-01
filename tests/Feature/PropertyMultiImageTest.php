<?php

use App\Models\Agent;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function makeMultiImageTestAgent(string $email): array
{
    $agent = Agent::create([
        'name' => 'Agent Tester',
        'email' => $email,
        'phone' => '+251911223344',
        'bio' => 'Experienced agent',
    ]);

    $account = User::factory()->create([
        'name' => 'Agent Tester',
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $agent->forceFill(['user_id' => $account->id])->save();

    return [$agent, $account];
}

test('admin can create a property with multiple images', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/dashboard/properties', [
        'title' => 'Luxury Bole Penthouse',
        'description' => 'A stunning penthouse with breathtaking skyline views.',
        'price' => 15000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 320,
        'type' => 'sale',
        'category' => 'apartment',
        'status' => 'published',
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanealem',
        'featured' => 1,
        'images' => [
            UploadedFile::fake()->image('living_room.jpg'),
            UploadedFile::fake()->image('master_bedroom.jpg'),
            UploadedFile::fake()->image('balcony_view.jpg'),
        ],
    ]);

    $response->assertRedirect(route('dashboard'));

    $property = Property::where('title', 'Luxury Bole Penthouse')->first();
    expect($property)->not->toBeNull();
    expect($property->images()->count())->toBe(3);
    expect($property->image_path)->not->toBeNull();
    expect(count($property->gallery_images))->toBe(3);

    foreach ($property->images as $img) {
        Storage::disk('public')->assertExists($img->image_path);
    }
});

test('admin can delete an individual image from a property', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);

    $property = Property::create([
        'title' => 'Old Airport Villa',
        'slug' => 'old-airport-villa',
        'price' => 20000000,
        'bedrooms' => 5,
        'bathrooms' => 4,
        'area' => 450,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
    ]);

    $file1 = UploadedFile::fake()->image('photo1.jpg')->store('properties', 'public');
    $file2 = UploadedFile::fake()->image('photo2.jpg')->store('properties', 'public');

    $img1 = $property->images()->create(['image_path' => $file1, 'sort_order' => 1]);
    $img2 = $property->images()->create(['image_path' => $file2, 'sort_order' => 2]);
    $property->update(['image_path' => $file1]);

    Storage::disk('public')->assertExists($file1);
    Storage::disk('public')->assertExists($file2);

    $response = $this->actingAs($admin)->delete("/dashboard/properties/{$property->id}/images/{$img1->id}");
    $response->assertRedirect();

    expect(PropertyImage::whereKey($img1->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing($file1);
    Storage::disk('public')->assertExists($file2);

    $property->refresh();
    expect($property->image_path)->toBe($file2);
});

test('agent can record a property with multiple images in the portal', function () {
    Storage::fake('public');

    [$agent, $account] = makeMultiImageTestAgent('agent.multi@example.com');

    $response = $this->actingAs($account)->post('/agent-portal/properties', [
        'title' => 'Harar Heritage Residence',
        'description' => 'Traditional architecture with modern luxury comforts.',
        'price' => 8500000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 190,
        'type' => 'sale',
        'category' => 'home',
        'status' => 'published',
        'city' => 'Harar',
        'address' => 'Jugol Historic Center',
        'images' => [
            UploadedFile::fake()->image('exterior.jpg'),
            UploadedFile::fake()->image('courtyard.jpg'),
        ],
    ]);

    $response->assertRedirect(route('agent.portal', ['section' => 'listings']));

    $property = Property::where('title', 'Harar Heritage Residence')->first();
    expect($property)->not->toBeNull();
    expect($property->agent_id)->toBe($agent->id);
    expect($property->images()->count())->toBe(2);

    foreach ($property->images as $img) {
        Storage::disk('public')->assertExists($img->image_path);
    }
});

test('agent can remove an image from their own property', function () {
    Storage::fake('public');

    [$agent, $account] = makeMultiImageTestAgent('agent.owner@example.com');

    $property = Property::create([
        'title' => 'Agent Townhouse',
        'slug' => 'agent-townhouse',
        'price' => 4500000,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 120,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $file = UploadedFile::fake()->image('living.jpg')->store('properties', 'public');
    $img = $property->images()->create(['image_path' => $file, 'sort_order' => 1]);

    $response = $this->actingAs($account)->delete("/agent-portal/properties/{$property->id}/images/{$img->id}");
    $response->assertRedirect();

    expect(PropertyImage::whereKey($img->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing($file);
});

test('agent cannot delete an image from another agent property', function () {
    Storage::fake('public');

    [$agent1] = makeMultiImageTestAgent('agent1@example.com');
    [, $account2] = makeMultiImageTestAgent('agent2@example.com');

    $property = Property::create([
        'title' => 'Agent 1 Property',
        'slug' => 'agent-1-property',
        'price' => 3000000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 100,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent1->id,
    ]);

    $file = UploadedFile::fake()->image('test.jpg')->store('properties', 'public');
    $img = $property->images()->create(['image_path' => $file, 'sort_order' => 1]);

    $response = $this->actingAs($account2)->delete("/agent-portal/properties/{$property->id}/images/{$img->id}");
    $response->assertForbidden();

    expect(PropertyImage::whereKey($img->id)->exists())->toBeTrue();
    Storage::disk('public')->assertExists($file);
});

test('homepage renders multi-image slider and sidebar drawer structure', function () {
    $property = Property::create([
        'title' => 'Grand Palace Villa',
        'slug' => 'grand-palace-villa',
        'price' => 35000000,
        'bedrooms' => 6,
        'bathrooms' => 5,
        'area' => 600,
        'type' => 'sale',
        'category' => 'villa',
        'status' => 'published',
        'featured' => true,
        'is_active' => true,
    ]);

    $property->images()->create(['image_path' => 'properties/dummy1.jpg', 'sort_order' => 1]);
    $property->images()->create(['image_path' => 'properties/dummy2.jpg', 'sort_order' => 2]);

    $response = $this->get('/');
    $response->assertOk();

    // Verify multi-image slider and quickview attributes are rendered
    $response->assertSee('data-property-slider', false);
    $response->assertSee('data-quickview-btn', false);
    $response->assertSee('data-property-json', false);

    // Verify sidebar drawer markup
    $response->assertSee('id="property-sidebar-drawer"', false);
    $response->assertSee('id="property-sidebar-backdrop"', false);
    $response->assertSee('id="sidebar-gallery-viewport"', false);
    $response->assertSee('id="sidebar-slider-prev"', false);
    $response->assertSee('id="sidebar-slider-next"', false);
    $response->assertSee('Grand Palace Villa');
});

test('admin and agent can record properties with many images (10+ photos)', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);

    $images = [];
    for ($i = 1; $i <= 10; $i++) {
        $images[] = UploadedFile::fake()->image("photo_{$i}.jpg");
    }

    $response = $this->actingAs($admin)->post('/dashboard/properties', [
        'title' => 'Mega Multi-Photo Penthouse',
        'description' => 'A property with ten photos uploaded in one go.',
        'price' => 50000000,
        'bedrooms' => 6,
        'bathrooms' => 5,
        'area' => 600,
        'type' => 'sale',
        'category' => 'apartment',
        'status' => 'published',
        'city' => 'Addis Ababa',
        'address' => 'Bole Sub City',
        'images' => $images,
    ]);

    $response->assertRedirect(route('dashboard'));

    $property = Property::where('title', 'Mega Multi-Photo Penthouse')->first();
    expect($property)->not->toBeNull();
    expect($property->images()->count())->toBe(10);
    expect(count($property->gallery_images))->toBe(10);

    foreach ($property->images as $img) {
        Storage::disk('public')->assertExists($img->image_path);
    }
});

test('properties listing and agent detail page render multi-image sliders for properties with multiple images', function () {
    $agent = Agent::create([
        'name' => 'Sara Multi Agent',
        'email' => 'sara.multi@example.com',
        'phone' => '+251911998877',
        'is_active' => true,
    ]);

    $property = Property::create([
        'title' => 'Sunset Hills Villa',
        'slug' => 'sunset-hills-villa',
        'price' => 18000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 320,
        'type' => 'sale',
        'category' => 'villa',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $property->images()->create(['image_path' => 'properties/sunset_1.jpg', 'sort_order' => 1]);
    $property->images()->create(['image_path' => 'properties/sunset_2.jpg', 'sort_order' => 2]);

    $indexResponse = $this->get('/properties');
    $indexResponse->assertOk()
        ->assertSee('data-property-slider', false)
        ->assertSee('data-property-slide', false)
        ->assertSee('Sunset Hills Villa');

    $agentResponse = $this->get("/agents/{$agent->id}");
    $agentResponse->assertOk()
        ->assertSee('data-property-slider', false)
        ->assertSee('data-property-slide', false)
        ->assertSee('Sunset Hills Villa');
});

test('property with no images defaults to luxury background image in gallery and primary url', function () {
    $property = Property::create([
        'title' => 'Background Luxury Loft',
        'slug' => 'background-luxury-loft',
        'price' => 12000000,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 150,
        'type' => 'sale',
        'category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
    ]);

    expect($property->images()->count())->toBe(0);
    expect($property->image_path)->toBeNull();
    expect($property->primary_image_url)->toContain('images/luxury/');
    expect(count($property->gallery_images))->toBe(1);
    expect($property->gallery_images[0])->toContain('images/luxury/');
});

test('admin can set an existing gallery image as primary cover image via route', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $property = Property::create([
        'title' => 'Cover Photo Test House',
        'slug' => 'cover-photo-test-house',
        'price' => 9500000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 200,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
    ]);

    $img1 = $property->images()->create(['image_path' => 'properties/photo1.jpg', 'sort_order' => 1]);
    $img2 = $property->images()->create(['image_path' => 'properties/photo2.jpg', 'sort_order' => 2]);
    $property->update(['image_path' => 'properties/photo1.jpg']);

    $response = $this->actingAs($admin)->postJson("/dashboard/properties/{$property->id}/images/{$img2->id}/cover");
    $response->assertOk()
        ->assertJson([
            'success' => true,
            'image_id' => $img2->id,
        ]);

    $property->refresh();
    expect($property->image_path)->toBe('properties/photo2.jpg');
    expect($img2->fresh()->sort_order)->toBe(1);
});

test('admin can set a luxury preset as cover image via route', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $property = Property::create([
        'title' => 'Preset Cover Test Villa',
        'slug' => 'preset-cover-test-villa',
        'price' => 14000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 280,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->postJson("/dashboard/properties/{$property->id}/preset-cover", [
        'preset' => 'luxury-towers',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'preset' => 'luxury-towers',
        ]);

    $property->refresh();
    expect($property->image_path)->toBe('images/luxury/luxury-towers.jpg');
    expect($property->images()->where('image_path', 'images/luxury/luxury-towers.jpg')->exists())->toBeTrue();
});

test('agent can set cover image and preset background on their assigned property', function () {
    [$agent, $account] = makeMultiImageTestAgent('agent.cover@example.com');

    $property = Property::create([
        'title' => 'Agent Managed Cover Property',
        'slug' => 'agent-managed-cover-property',
        'price' => 8500000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 190,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $img1 = $property->images()->create(['image_path' => 'properties/ag1.jpg', 'sort_order' => 1]);
    $img2 = $property->images()->create(['image_path' => 'properties/ag2.jpg', 'sort_order' => 2]);
    $property->update(['image_path' => 'properties/ag1.jpg']);

    // Set image 2 as cover
    $response = $this->actingAs($account)->postJson("/agent-portal/properties/{$property->id}/images/{$img2->id}/cover");
    $response->assertOk()->assertJson(['success' => true]);

    $property->refresh();
    expect($property->image_path)->toBe('properties/ag2.jpg');

    // Set preset as cover
    $presetResponse = $this->actingAs($account)->postJson("/agent-portal/properties/{$property->id}/preset-cover", [
        'preset' => 'panoramic-park',
    ]);
    $presetResponse->assertOk()->assertJson(['success' => true, 'preset' => 'panoramic-park']);

    $property->refresh();
    expect($property->image_path)->toBe('images/luxury/panoramic-park.jpg');
});

test('agent cannot set cover image on property of another agent', function () {
    [$agent1, $account1] = makeMultiImageTestAgent('agent1@example.com');
    [$agent2, $account2] = makeMultiImageTestAgent('agent2@example.com');

    $property = Property::create([
        'title' => 'Agent 2 Protected Property',
        'slug' => 'agent-2-protected-property',
        'price' => 11000000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 210,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent2->id,
    ]);

    $img = $property->images()->create(['image_path' => 'properties/ag2_only.jpg', 'sort_order' => 1]);

    $response = $this->actingAs($account1)->postJson("/agent-portal/properties/{$property->id}/images/{$img->id}/cover");
    $response->assertForbidden();
});

test('admin can update property cover image via edit form with cover_image_id', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $property = Property::create([
        'title' => 'Form Update Cover Villa',
        'slug' => 'form-update-cover-villa',
        'price' => 17000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 310,
        'type' => 'sale',
        'category' => 'villa',
        'status' => 'published',
        'is_active' => true,
    ]);

    $img1 = $property->images()->create(['image_path' => 'properties/form1.jpg', 'sort_order' => 1]);
    $img2 = $property->images()->create(['image_path' => 'properties/form2.jpg', 'sort_order' => 2]);
    $property->update(['image_path' => 'properties/form1.jpg']);

    $response = $this->actingAs($admin)->put("/dashboard/properties/{$property->id}", [
        'title' => 'Form Update Cover Villa Updated',
        'price' => 17500000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 310,
        'type' => 'sale',
        'category' => 'villa',
        'status' => 'published',
        'cover_image_id' => $img2->id,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('dashboard'));

    $property->refresh();
    expect($property->image_path)->toBe('properties/form2.jpg');
    expect($img2->fresh()->sort_order)->toBe(1);
});

test('agent can update property cover preset via edit form', function () {
    [$agent, $account] = makeMultiImageTestAgent('agent.presetform@example.com');

    $property = Property::create([
        'title' => 'Agent Preset Form Property',
        'slug' => 'agent-preset-form-property',
        'price' => 9000000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 180,
        'type' => 'sale',
        'category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $response = $this->actingAs($account)->put("/agent-portal/properties/{$property->id}", [
        'title' => 'Agent Preset Form Property Updated',
        'price' => 9200000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 180,
        'type' => 'sale',
        'category' => 'apartment',
        'status' => 'published',
        'cover_preset' => 'hero-skyline',
    ]);

    $response->assertRedirect(route('agent.portal', ['section' => 'listings']));

    $property->refresh();
    expect($property->image_path)->toBe('images/luxury/hero-skyline.jpg');
    expect($property->images()->where('image_path', 'images/luxury/hero-skyline.jpg')->exists())->toBeTrue();
});
