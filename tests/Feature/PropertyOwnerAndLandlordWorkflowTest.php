<?php

use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyImage;
use App\Models\PropertyOffer;
use App\Models\RentSchedule;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');
});

// ── 1. DASHBOARD ACCESS & BOUNDARY CONTROLS ───────────────────────────────────

test('authenticated property owner can access seller dashboard with unified shell', function () {
    $owner = User::factory()->create([
        'name' => 'Abebe Owner',
        'email' => 'abebe.owner@example.com',
        'role' => 'seller',
    ]);

    $response = $this->actingAs($owner)->get(route('seller.dashboard'));

    $response->assertOk()
        ->assertSee('Abebe Owner')
        ->assertSee('Property Owner &amp; Landlord Dashboard', false)
        ->assertSee('My Properties')
        ->assertSee('Media Studio')
        ->assertSee('Document Vault')
        ->assertSee('Rent Collection');
});

test('admin cannot open individual seller dashboard due to boundary enforcement', function () {
    $admin = User::factory()->create([
        'name' => 'Platform Admin',
        'email' => 'admin@example.com',
        'role' => 'admin',
    ]);

    $this->actingAs($admin)->get(route('seller.dashboard'))
        ->assertForbidden();
});

test('suspended seller cannot access seller dashboard', function () {
    $suspendedOwner = User::factory()->create([
        'role' => 'seller',
        'status' => 'suspended',
    ]);

    $this->actingAs($suspendedOwner)->get(route('seller.dashboard'))
        ->assertForbidden();
});

test('legacy landlord and property-owner URLs redirect to unified seller dashboard', function () {
    $owner = User::factory()->create(['role' => 'landlord']);

    $this->actingAs($owner)->get('/dashboard/landlord')
        ->assertRedirect(route('seller.dashboard'));

    $this->actingAs($owner)->get('/dashboard/property-owner')
        ->assertRedirect(route('seller.dashboard'));
});

// ── 2. PROPERTY LISTINGS CRUD & LIFECYCLE ─────────────────────────────────────

test('property owner can create property listing with audit log', function () {
    $owner = User::factory()->create(['role' => 'seller']);

    $response = $this->actingAs($owner)->post(route('seller.listings.store'), [
        'title' => 'Bole Medhanealem Luxury Penthouse',
        'description' => 'Spacious 4 bedroom penthouse with panoramic city view and modern finishing.',
        'price' => 12500000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 280,
        'building_area' => 260,
        'floors' => 2,
        'parking_spaces' => 2,
        'balconies' => 2,
        'year_built' => 2024,
        'condition' => 'new',
        'type' => 'sale',
        'category' => 'apartment',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'status' => 'published',
        'has_water' => '1',
        'has_electricity' => '1',
        'has_generator' => '1',
        'has_water_tank' => '1',
        'image' => UploadedFile::fake()->image('penthouse_cover.jpg', 800, 600),
    ]);

    $response->assertRedirect(route('seller.dashboard'));

    $property = Property::where('user_id', $owner->id)->where('title', 'Bole Medhanealem Luxury Penthouse')->first();
    expect($property)->not->toBeNull()
        ->and((float) $property->price)->toBe(12500000.0)
        ->and($property->is_active)->toBeTrue()
        ->and($property->image_path)->not->toBeNull()
        ->and(Storage::disk('public')->exists($property->image_path))->toBeTrue();

    $log = AuditLog::where('user_id', $owner->id)->where('action', 'seller.property_created')->first();
    expect($log)->not->toBeNull()
        ->and($log->reason)->toContain('Bole Medhanealem Luxury Penthouse');
});

test('property owner can update their own property listing', function () {
    $owner = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create([
        'user_id' => $owner->id,
        'price' => 8000000,
        'title' => 'Original Villa',
    ]);

    $response = $this->actingAs($owner)->put(route('seller.listings.update', $property), [
        'title' => 'Updated Bole Villa',
        'description' => 'Updated description with upgraded kitchen and generator backup installed.',
        'price' => 9500000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 200,
        'building_area' => 180,
        'floors' => 1,
        'parking_spaces' => 2,
        'balconies' => 1,
        'condition' => 'excellent',
        'type' => 'sale',
        'category' => 'villa',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'status' => 'published',
    ]);

    $response->assertRedirect(route('seller.dashboard'));
    $fresh = $property->fresh();
    expect($fresh->title)->toBe('Updated Bole Villa')
        ->and((float) $fresh->price)->toBe(9500000.0);

    $log = AuditLog::where('user_id', $owner->id)->where('action', 'seller.property_updated')->first();
    expect($log)->not->toBeNull();
});

test('property owner cannot update another users property', function () {
    $owner1 = User::factory()->create(['role' => 'seller']);
    $owner2 = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create(['user_id' => $owner1->id]);

    $this->actingAs($owner2)->put(route('seller.listings.update', $property), [
        'title' => 'Hacked Title',
        'price' => 100,
    ])->assertForbidden();
});

test('property owner can delete their own property', function () {
    $owner = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($owner)->delete(route('seller.properties.destroy', $property));
    $response->assertRedirect(route('seller.dashboard'));

    expect(Property::find($property->id))->toBeNull();
    $log = AuditLog::where('user_id', $owner->id)->where('action', 'seller.property_deleted')->first();
    expect($log)->not->toBeNull();
});

test('property owner cannot delete another users property', function () {
    $owner1 = User::factory()->create(['role' => 'seller']);
    $owner2 = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create(['user_id' => $owner1->id]);

    $this->actingAs($owner2)->delete(route('seller.properties.destroy', $property))
        ->assertForbidden();
});

test('property owner can toggle publish status and change status transitions', function () {
    $owner = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create([
        'user_id' => $owner->id,
        'is_active' => true,
        'status' => 'published',
    ]);

    // 1. Toggle publish off
    $this->actingAs($owner)->post(route('seller.listings.publish', $property))
        ->assertRedirect(route('seller.dashboard'));
    expect($property->fresh()->is_active)->toBeFalse()
        ->and($property->fresh()->status)->toBe('draft');

    // 2. Toggle publish on
    $this->actingAs($owner)->post(route('seller.listings.publish', $property))
        ->assertRedirect(route('seller.dashboard'));
    expect($property->fresh()->is_active)->toBeTrue()
        ->and($property->fresh()->status)->toBe('published');

    // 3. Status transition to paused
    $this->actingAs($owner)->post(route('seller.listings.status', [$property, 'paused']))
        ->assertRedirect(route('seller.dashboard'));
    expect($property->fresh()->status)->toBe('paused')
        ->and($property->fresh()->is_active)->toBeFalse();

    // 4. Status transition to sold
    $this->actingAs($owner)->post(route('seller.listings.status', [$property, 'sold']))
        ->assertRedirect(route('seller.dashboard'));
    expect($property->fresh()->status)->toBe('sold');
});

// ── 3. MEDIA & PHOTO STUDIO ──────────────────────────────────────────────────

test('property owner can upload gallery photos, set cover photo, and delete photos', function () {
    $owner = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create(['user_id' => $owner->id]);

    // 1. Upload photos
    $response = $this->actingAs($owner)->post(route('seller.photos.upload', $property), [
        'photos' => [
            UploadedFile::fake()->image('living_room.jpg', 600, 400),
            UploadedFile::fake()->image('kitchen.jpg', 600, 400),
        ],
    ]);
    $response->assertRedirect(route('seller.dashboard'));

    expect($property->images()->count())->toBe(2);
    $firstImage = $property->images()->first();
    expect(Storage::disk('public')->exists($firstImage->image_path))->toBeTrue();

    // 2. Set cover photo
    $coverResponse = $this->actingAs($owner)->post(route('seller.photos.cover', $firstImage));
    $coverResponse->assertRedirect(route('seller.dashboard'));
    expect($property->fresh()->image_path)->toBe($firstImage->image_path);

    // 3. Delete photo
    $deleteResponse = $this->actingAs($owner)->delete(route('seller.photos.destroy', $firstImage));
    $deleteResponse->assertRedirect(route('seller.dashboard'));
    expect(PropertyImage::find($firstImage->id))->toBeNull()
        ->and(Storage::disk('public')->exists($firstImage->image_path))->toBeFalse();
});

test('property owner cannot delete photos belonging to another owners property', function () {
    $owner1 = User::factory()->create(['role' => 'seller']);
    $owner2 = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create(['user_id' => $owner1->id]);
    $image = $property->images()->create([
        'image_path' => 'properties/gallery/test.jpg',
        'sort_order' => 1,
    ]);

    $this->actingAs($owner2)->delete(route('seller.photos.destroy', $image))
        ->assertForbidden();
});

// ── 4. DOCUMENT VAULT WITH ACCESS LEVELS & VERSIONS ──────────────────────────

test('property owner can upload ownership documents with access levels and versions', function () {
    $owner = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create(['user_id' => $owner->id]);

    // Version 1 upload
    $response1 = $this->actingAs($owner)->post(route('seller.documents.upload', $property), [
        'document_type' => 'title_deed',
        'title' => 'Official Addis Ababa Land Title Deed',
        'access_level' => 'verified_only',
        'notes' => 'Authentic title deed issued by Addis Ababa Land Administration.',
        'file' => UploadedFile::fake()->create('title_deed_v1.pdf', 500, 'application/pdf'),
    ]);
    $response1->assertRedirect(route('seller.dashboard'));

    $doc1 = PropertyDocument::where('property_id', $property->id)->first();
    expect($doc1)->not->toBeNull()
        ->and($doc1->version)->toBe(1)
        ->and($doc1->access_level)->toBe('verified_only')
        ->and(Storage::disk('public')->exists($doc1->file_path))->toBeTrue();

    // Version 2 upload (same document_type)
    $response2 = $this->actingAs($owner)->post(route('seller.documents.upload', $property), [
        'document_type' => 'title_deed',
        'title' => 'Renewed Title Deed 2026',
        'access_level' => 'private',
        'notes' => 'Updated stamp after renewal.',
        'file' => UploadedFile::fake()->create('title_deed_v2.pdf', 600, 'application/pdf'),
    ]);
    $response2->assertRedirect(route('seller.dashboard'));

    $doc2 = PropertyDocument::where('property_id', $property->id)->where('version', 2)->first();
    expect($doc2)->not->toBeNull()
        ->and($doc2->version)->toBe(2)
        ->and($doc2->access_level)->toBe('private');

    // Delete document
    $deleteResponse = $this->actingAs($owner)->delete(route('seller.documents.destroy', $doc1));
    $deleteResponse->assertRedirect(route('seller.dashboard'));
    expect(PropertyDocument::find($doc1->id))->toBeNull();
});

test('property owner cannot delete document belonging to another users property', function () {
    $owner1 = User::factory()->create(['role' => 'seller']);
    $owner2 = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create(['user_id' => $owner1->id]);
    $doc = $property->documents()->create([
        'user_id' => $owner1->id,
        'document_type' => 'title_deed',
        'title' => 'Protected Deed',
        'document_number' => 'DOC-12345',
        'version' => 1,
        'file_path' => 'property_documents/doc.pdf',
        'file_name' => 'doc.pdf',
        'file_size' => 1024,
        'mime_type' => 'application/pdf',
        'access_level' => 'private',
    ]);

    $this->actingAs($owner2)->delete(route('seller.documents.destroy', $doc))
        ->assertForbidden();
});

// ── 5. INQUIRIES & OFFERS INTERACTION ────────────────────────────────────────

test('property owner can reply to inquiry on their property', function () {
    $owner = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create(['user_id' => $owner->id]);

    $inquiry = Inquiry::create([
        'property_id' => $property->id,
        'name' => 'Bereket Buyer',
        'email' => 'bereket@example.com',
        'phone' => '+251911445566',
        'subject' => 'Viewing Inquiry',
        'message' => 'Is this property still available for viewing this Saturday?',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($owner)->post(route('seller.inquiries.reply', $inquiry), [
        'reply_message' => 'Yes Bereket, the villa is available this Saturday at 2:00 PM.',
    ]);
    $response->assertRedirect(route('seller.dashboard'));

    expect($inquiry->fresh()->status)->toBe('responded')
        ->and(str_contains((string) $inquiry->fresh()->message, 'Saturday at 2:00 PM'))->toBeTrue();
});

test('property owner cannot reply to inquiry on another owners property', function () {
    $owner1 = User::factory()->create(['role' => 'seller']);
    $owner2 = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create(['user_id' => $owner1->id]);

    $inquiry = Inquiry::create([
        'property_id' => $property->id,
        'name' => 'Client',
        'email' => 'client@example.com',
        'subject' => 'General Question',
        'message' => 'Question',
    ]);

    $this->actingAs($owner2)->post(route('seller.inquiries.reply', $inquiry), [
        'reply_message' => 'Unauthorized reply',
    ])->assertForbidden();
});

test('property owner can accept or counter offer on their property', function () {
    $owner = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    $property = Property::factory()->create(['user_id' => $owner->id, 'price' => 5000000]);

    $offer = PropertyOffer::create([
        'property_id' => $property->id,
        'user_id' => $buyer->id,
        'buyer_name' => $buyer->name,
        'buyer_email' => $buyer->email,
        'buyer_phone' => '+251911223344',
        'offer_type' => 'purchase',
        'original_price' => 5000000,
        'offered_price' => 4500000,
        'financing_type' => 'cash',
        'status' => 'submitted',
    ]);

    // 1. Counter offer
    $counterResponse = $this->actingAs($owner)->post(route('offers.counter', $offer), [
        'counter_amount' => 4800000,
        'message' => 'Best I can do is 4.8M ETB.',
    ]);
    $counterResponse->assertRedirect();
    expect($offer->fresh()->status)->toBe('countered')
        ->and((float) $offer->fresh()->current_counter_amount)->toBe(4800000.0);

    // 2. Accept offer
    $acceptResponse = $this->actingAs($owner)->put(route('offers.accept', $offer));
    $acceptResponse->assertRedirect();
    expect($offer->fresh()->status)->toBe('accepted');
});

// ── 6. LANDLORD LEASES, RENT & SECURITY DEPOSITS ─────────────────────────────

test('landlord can create, renew, and terminate lease agreements', function () {
    $landlord = User::factory()->create(['role' => 'landlord']);
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create(['user_id' => $landlord->id, 'type' => 'rent']);

    // 1. Create lease
    $createResponse = $this->actingAs($landlord)->post(route('seller.leases.store'), [
        'property_id' => $property->id,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'tenant_phone' => '+251911223344',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(12)->toDateString(),
        'duration_months' => 12,
        'monthly_rent' => 25000,
        'security_deposit' => 50000,
        'payment_frequency' => 'monthly',
        'terms_and_conditions' => 'Standard residential tenancy contract.',
    ]);
    $createResponse->assertRedirect(route('seller.dashboard'));

    $lease = Lease::where('landlord_user_id', $landlord->id)->where('property_id', $property->id)->first();
    expect($lease)->not->toBeNull()
        ->and((float) $lease->monthly_rent)->toBe(25000.0)
        ->and((float) $lease->security_deposit)->toBe(50000.0)
        ->and($lease->deposit_status)->toBe('held')
        ->and($lease->status)->toBe('draft');

    // 2. Renew lease
    $renewResponse = $this->actingAs($landlord)->put(route('seller.leases.renew', $lease), [
        'new_start_date' => now()->addMonths(12)->toDateString(),
        'new_end_date' => now()->addMonths(24)->toDateString(),
        'new_monthly_rent' => 28000,
    ]);
    $renewResponse->assertRedirect(route('seller.dashboard'));
    expect((float) $lease->fresh()->monthly_rent)->toBe(28000.0)
        ->and($lease->fresh()->status)->toBe('active');

    // 3. Terminate lease
    $terminateResponse = $this->actingAs($landlord)->put(route('seller.leases.terminate', $lease), [
        'termination_reason' => 'Tenant relocating to another region.',
    ]);
    $terminateResponse->assertRedirect(route('seller.dashboard'));
    expect($lease->fresh()->status)->toBe('terminated')
        ->and($lease->fresh()->notes)->toContain('relocating');
});

test('landlord cannot create lease for property they do not own', function () {
    $landlord1 = User::factory()->create(['role' => 'landlord']);
    $landlord2 = User::factory()->create(['role' => 'landlord']);
    $property = Property::factory()->create(['user_id' => $landlord1->id]);

    $this->actingAs($landlord2)->post(route('seller.leases.store'), [
        'property_id' => $property->id,
        'tenant_name' => 'John',
        'tenant_email' => 'john@example.com',
        'tenant_phone' => '+251911000000',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(6)->toDateString(),
        'duration_months' => 6,
        'monthly_rent' => 20000,
        'security_deposit' => 40000,
    ])->assertForbidden();
});

test('landlord can record offline rent payment and mark installments overdue', function () {
    $landlord = User::factory()->create(['role' => 'landlord']);
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create(['user_id' => $landlord->id]);

    $lease = Lease::create([
        'property_id' => $property->id,
        'landlord_user_id' => $landlord->id,
        'tenant_user_id' => $tenant->id,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'tenant_phone' => '+251911000000',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(12)->toDateString(),
        'duration_months' => 12,
        'monthly_rent' => 30000,
        'security_deposit' => 60000,
        'status' => 'active',
    ]);

    $schedule = RentSchedule::create([
        'lease_id' => $lease->id,
        'installment_number' => 1,
        'period_label' => 'January 2026',
        'due_date' => now()->toDateString(),
        'amount_due' => 30000,
        'amount_paid' => 0,
        'status' => 'pending',
    ]);

    // 1. Mark overdue
    $this->actingAs($landlord)->put(route('seller.rent.mark-late', $schedule))
        ->assertRedirect(route('seller.dashboard'));
    expect($schedule->fresh()->status)->toBe('overdue');

    // 2. Record payment
    $recordResponse = $this->actingAs($landlord)->post(route('seller.rent.record', $schedule), [
        'amount_paid' => 30000,
        'payment_method' => 'telebirr',
        'notes' => 'Received via Telebirr merchant payment',
    ]);
    $recordResponse->assertRedirect(route('seller.dashboard'));

    expect($schedule->fresh()->status)->toBe('paid')
        ->and((float) $schedule->fresh()->amount_paid)->toBe(30000.0)
        ->and($schedule->fresh()->payment_method)->toBe('telebirr')
        ->and($schedule->fresh()->paid_at)->not->toBeNull();
});

test('landlord can deduct from security deposit with itemized reason and return remaining balance', function () {
    $landlord = User::factory()->create(['role' => 'landlord']);
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create(['user_id' => $landlord->id]);

    $lease = Lease::create([
        'property_id' => $property->id,
        'landlord_user_id' => $landlord->id,
        'tenant_user_id' => $tenant->id,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'tenant_phone' => '+251911000000',
        'start_date' => now()->subYear()->toDateString(),
        'end_date' => now()->toDateString(),
        'duration_months' => 12,
        'monthly_rent' => 20000,
        'security_deposit' => 40000,
        'deposit_status' => 'held',
        'deposit_deductions' => 0,
        'deposit_refunded' => 0,
        'status' => 'active',
    ]);

    // 1. Deduct for damage
    $deductResponse = $this->actingAs($landlord)->post(route('seller.deposits.deduct', $lease), [
        'deduction_amount' => 12000,
        'deduction_reason' => 'Broken window pane in master bedroom and water tap replacement.',
    ]);
    $deductResponse->assertRedirect(route('seller.dashboard'));

    $freshLease = $lease->fresh();
    expect((float) $freshLease->deposit_deductions)->toBe(12000.0)
        ->and($freshLease->deposit_status)->toBe('partially_deducted')
        ->and($freshLease->deposit_deduction_reason)->toContain('Broken window pane');

    // 2. Return remaining deposit (40000 - 12000 = 28000)
    $returnResponse = $this->actingAs($landlord)->post(route('seller.deposits.return', $lease));
    $returnResponse->assertRedirect(route('seller.dashboard'));

    $finalLease = $lease->fresh();
    expect((float) $finalLease->deposit_refunded)->toBe(28000.0)
        ->and($finalLease->deposit_status)->toBe('returned')
        ->and($finalLease->deposit_returned_at)->not->toBeNull();
});

// ── 7. MAINTENANCE TICKETS DISPATCH ──────────────────────────────────────────

test('landlord can assign maintenance ticket to contractor and mark completed', function () {
    $landlord = User::factory()->create(['role' => 'landlord']);
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create(['user_id' => $landlord->id]);

    $ticket = MaintenanceTicket::create([
        'user_id' => $tenant->id,
        'property_id' => $property->id,
        'ticket_number' => 'TCK-'.strtoupper(fake()->unique()->bothify('##??')),
        'title' => 'Plumbing Leak Under Kitchen Sink',
        'category' => 'plumbing',
        'priority' => 'high',
        'description' => 'Water leaking steadily onto the cabinet floor.',
        'status' => 'open',
    ]);

    // 1. Assign contractor
    $assignResponse = $this->actingAs($landlord)->post(route('seller.maintenance.assign', $ticket), [
        'assigned_contractor' => 'Addis Home Services Plumbers',
        'location_notes' => 'Contractor scheduled to visit tomorrow at 10 AM.',
    ]);
    $assignResponse->assertRedirect(route('seller.dashboard'));

    expect($ticket->fresh()->status)->toBe('in_progress')
        ->and($ticket->fresh()->location_notes)->toContain('Addis Home Services Plumbers');

    // 2. Complete maintenance
    $completeResponse = $this->actingAs($landlord)->post(route('seller.maintenance.complete', $ticket), [
        'resolution_notes' => 'New PVC piping and valve installed. Leak resolved and tested.',
    ]);
    $completeResponse->assertRedirect(route('seller.dashboard'));

    expect($ticket->fresh()->status)->toBe('completed')
        ->and($ticket->fresh()->resolution_notes)->toContain('New PVC piping')
        ->and($ticket->fresh()->resolved_at)->not->toBeNull();
});

test('landlord cannot assign maintenance ticket for another landlords property', function () {
    $landlord1 = User::factory()->create(['role' => 'landlord']);
    $landlord2 = User::factory()->create(['role' => 'landlord']);
    $property = Property::factory()->create(['user_id' => $landlord1->id]);

    $ticket = MaintenanceTicket::create([
        'user_id' => $landlord1->id,
        'property_id' => $property->id,
        'ticket_number' => 'TCK-XYZ1',
        'title' => 'Electrical Short',
        'category' => 'electrical',
        'priority' => 'medium',
        'description' => 'Breaker tripping',
        'status' => 'open',
    ]);

    $this->actingAs($landlord2)->post(route('seller.maintenance.assign', $ticket), [
        'assigned_contractor' => 'Rival Electrician',
    ])->assertForbidden();
});
