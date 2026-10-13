<?php

use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\PropertyReport;
use App\Models\PropertyReview;
use App\Models\SavedCollection;
use App\Models\User;
use Illuminate\Support\Str;

function makeEcosystemProperty(array $attributes = []): Property
{
    $title = $attributes['title'] ?? 'Ecosystem Test Property';

    return Property::create(array_merge([
        'title' => $title,
        'slug' => Str::slug($title).'-'.uniqid(),
        'price' => 12000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 200,
        'building_area' => 180,
        'type' => 'sale',
        'property_category' => 'villa',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'address' => 'Bole Rwanda',
        'has_electricity' => true,
        'has_water' => true,
        'has_generator' => true,
        'has_water_tank' => true,
        'has_solar' => false,
        'has_security' => true,
    ], $attributes));
}

test('property comparison matrix works with add, view and clear actions', function () {
    $prop1 = makeEcosystemProperty(['title' => 'Prime Villa One', 'price' => 25000000, 'area' => 300]);
    $prop2 = makeEcosystemProperty(['title' => 'Luxury Villa Two', 'price' => 30000000, 'area' => 350]);

    // 1. Toggle add prop1
    $toggleRes1 = $this->post(route('properties.compare.toggle', $prop1));
    $toggleRes1->assertRedirect();
    expect(session('compared_property_ids'))->toContain($prop1->id);

    // 2. Toggle add prop2
    $toggleRes2 = $this->post(route('properties.compare.toggle', $prop2));
    $toggleRes2->assertRedirect();
    expect(session('compared_property_ids'))->toContain($prop1->id, $prop2->id);

    // 3. View comparison matrix
    $matrixRes = $this->get(route('properties.compare'));
    $matrixRes->assertOk();
    $matrixRes->assertSee('Prime Villa One');
    $matrixRes->assertSee('Luxury Villa Two');
    $matrixRes->assertSee('Listed Price');
    $matrixRes->assertSee('/ m²');

    // 4. View comparison matrix using explicit query ids
    $queryRes = $this->get(route('properties.compare', ['ids' => "{$prop1->id},{$prop2->id}"]));
    $queryRes->assertOk();
    $queryRes->assertSee('Prime Villa One');
    $queryRes->assertSee('Luxury Villa Two');

    // 5. AJAX json toggle test
    $jsonRes = $this->postJson(route('properties.compare.toggle', $prop1));
    $jsonRes->assertOk();
    $jsonRes->assertJson([
        'success' => true,
        'action' => 'removed',
        'count' => 1,
    ]);

    // 6. Verify marked indicator renders on properties catalog
    $indexRes = $this->withSession(['compared_property_ids' => [$prop2->id]])->get(route('properties.index'));
    $indexRes->assertOk();
    $indexRes->assertSee('Marked for Matrix');
    $indexRes->assertSee('is-marked-for-compare');
    $indexRes->assertSee('floating-compare-bar');

    // 7. Clear comparison
    $clearRes = $this->post(route('properties.compare.clear'));
    $clearRes->assertRedirect();
    expect(session('compared_property_ids'))->toBeEmpty();
});

test('user can submit multi-criteria review and view ratings', function () {
    $user = User::factory()->create(['role' => 'client']);
    $property = makeEcosystemProperty();

    $response = $this->actingAs($user)->post(route('properties.reviews.store', $property), [
        'rating' => 5,
        'cleanliness_rating' => 5,
        'accuracy_rating' => 4,
        'communication_rating' => 5,
        'location_rating' => 5,
        'value_rating' => 4,
        'review_title' => 'Exceptional standard in Bole',
        'review_body' => 'The property exceeded our expectations with full utility backup and prime security.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('property_reviews', [
        'property_id' => $property->id,
        'user_id' => $user->id,
        'rating' => 5,
        'cleanliness_rating' => 5,
        'review_title' => 'Exceptional standard in Bole',
    ]);

    $freshProp = $property->fresh();
    expect($freshProp->reviews_count)->toBe(1)
        ->and((float) $freshProp->average_rating)->toBe(5.0);
});

test('admin can toggle review status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $property = makeEcosystemProperty();

    $review = PropertyReview::create([
        'property_id' => $property->id,
        'user_id' => $user->id,
        'rating' => 4,
        'cleanliness_rating' => 4,
        'accuracy_rating' => 4,
        'communication_rating' => 4,
        'location_rating' => 4,
        'value_rating' => 4,
        'review_body' => 'Very solid construction and peaceful neighborhood.',
        'status' => 'published',
    ]);

    $response = $this->actingAs($admin)->put(route('reviews.toggle', $review));
    $response->assertRedirect();

    expect($review->fresh()->status)->toBe('hidden');
});

test('user can report suspicious or scam listing to trust and safety', function () {
    $property = makeEcosystemProperty(['title' => 'Suspicious Penthouse']);

    $response = $this->post(route('properties.report', $property), [
        'reason' => 'scam',
        'description' => 'Seller requested an advance wire transfer before any property viewing could take place.',
        'contact_email' => 'whistleblower@example.com',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('property_reports', [
        'property_id' => $property->id,
        'reason' => 'scam',
        'contact_email' => 'whistleblower@example.com',
        'status' => 'pending',
    ]);

    $report = PropertyReport::where('property_id', $property->id)->first();
    expect($report->report_number)->toStartWith('REP-');
});

test('admin can investigate safety report and suspend malicious property', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $property = makeEcosystemProperty(['title' => 'Fraudulent Duplicate Villa', 'is_active' => true]);

    $report = PropertyReport::create([
        'property_id' => $property->id,
        'reason' => 'scam',
        'description' => 'Confirmed fraudulent deed copy.',
        'contact_email' => 'alert@example.com',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->put(route('reports.update', $report), [
        'status' => 'resolved',
        'resolution_notes' => 'Property suspended after land registry document check failed.',
        'suspend_property' => true,
    ]);

    $response->assertRedirect();

    expect($report->fresh()->status)->toBe('resolved')
        ->and($report->fresh()->resolved_by)->toBe($admin->id)
        ->and($property->fresh()->is_active)->toBeFalse();
});

test('tenant can submit maintenance ticket and manager can update status', function () {
    $tenant = User::factory()->create(['role' => 'client']);
    $manager = User::factory()->create(['role' => 'admin']);
    $property = makeEcosystemProperty();

    // 1. Submit maintenance request
    $submitRes = $this->actingAs($tenant)->post(route('properties.maintenance.store', $property), [
        'title' => 'Water pump failure in basement',
        'category' => 'plumbing',
        'priority' => 'emergency',
        'description' => 'The secondary booster pump stopped running, causing low water pressure on 2nd floor.',
        'location_notes' => 'Basement pump room next to generator',
    ]);

    $submitRes->assertRedirect();

    $this->assertDatabaseHas('maintenance_tickets', [
        'property_id' => $property->id,
        'user_id' => $tenant->id,
        'category' => 'plumbing',
        'priority' => 'emergency',
        'status' => 'open',
    ]);

    $ticket = MaintenanceTicket::where('property_id', $property->id)->first();
    expect($ticket->ticket_number)->toStartWith('MNT-');

    // 2. Manager updates status to in_progress
    $updateRes = $this->actingAs($manager)->put(route('maintenance.update-status', $ticket), [
        'status' => 'in_progress',
        'resolution_notes' => 'Technician dispatched to replace capacitor.',
    ]);

    $updateRes->assertRedirect();
    expect($ticket->fresh()->status)->toBe('in_progress');
});

test('user can organize properties into saved collections', function () {
    $user = User::factory()->create(['role' => 'client']);
    $property = makeEcosystemProperty(['title' => 'Dream Bole Residence']);

    // 1. Create collection with property
    $createRes = $this->actingAs($user)->post(route('collections.store'), [
        'name' => 'Bole Luxury Shortlist',
        'description' => 'Curated villas for family purchase',
        'is_public' => false,
        'property_id' => $property->id,
    ]);

    $createRes->assertRedirect();

    $this->assertDatabaseHas('saved_collections', [
        'user_id' => $user->id,
        'name' => 'Bole Luxury Shortlist',
    ]);

    $collection = SavedCollection::where('user_id', $user->id)->first();
    expect($collection->properties)->toHaveCount(1)
        ->and($collection->properties->first()->id)->toBe($property->id);

    // 2. Toggle property out of collection
    $toggleRes = $this->actingAs($user)->post(route('collections.toggleProperty', [$collection, $property]));
    $toggleRes->assertRedirect();
    expect($collection->fresh()->properties)->toHaveCount(0);

    // 3. Delete collection
    $deleteRes = $this->actingAs($user)->delete(route('collections.destroy', $collection));
    $deleteRes->assertRedirect();
    $this->assertDatabaseMissing('saved_collections', ['id' => $collection->id]);
});

test('property model correctly calculates completeness quality score and investment metrics', function () {
    $property = makeEcosystemProperty([
        'price' => 20000000,
        'area' => 250,
        'building_area' => 250,
        'year_built' => 2023,
        'image_path' => 'properties/sample.jpg',
        'description' => 'A premier modern villa located in the prestigious Bole district of Addis Ababa, featuring complete high-end finishings, backup utilities, and outstanding security.',
        'latitude' => 8.995,
        'longitude' => 38.785,
    ]);

    // Price per sqm
    expect($property->price_per_sqm)->toBe(80000.0);

    // Completeness quality score (0 - 100)
    expect($property->quality_score['score'])->toBeGreaterThanOrEqual(70);

    // Investment yield metrics
    $metrics = $property->investment_metrics;
    expect($metrics)->toHaveKeys([
        'monthly_rent_estimate',
        'annual_gross_income',
        'gross_yield_percentage',
        'net_operating_income',
        'net_yield_percentage',
    ])
        ->and($metrics['gross_yield_percentage'])->toBeGreaterThan(0);
});

test('natural language search engine parses price, bedrooms, category and location from query', function () {
    $matching = makeEcosystemProperty([
        'title' => 'Modern 3 Bedroom Apartment in Bole',
        'bedrooms' => 3,
        'price' => 8500000,
        'property_category' => 'apartment',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
    ]);

    $different = makeEcosystemProperty([
        'title' => 'Spacious 6 Bedroom Villa in Hawassa',
        'bedrooms' => 6,
        'price' => 35000000,
        'property_category' => 'villa',
        'city' => 'Hawassa',
    ]);

    $response = $this->get(route('properties.index', [
        'search' => '3 bedroom apartment in Bole under 10000000',
    ]));

    $response->assertOk();
    $response->assertSee('Modern 3 Bedroom Apartment in Bole');
    $response->assertDontSee('Spacious 6 Bedroom Villa in Hawassa');
});
