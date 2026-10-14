<?php

use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectUnit;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyOffer;
use App\Models\PropertyViewing;
use App\Models\SavedSearch;
use App\Models\UnitReservation;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');
});

// ── 1. GUEST / UNAUTHENTICATED CAPABILITIES ───────────────────────────────────

test('unauthenticated guest can browse properties, view details, and search stays', function () {
    $property = Property::factory()->create([
        'title' => 'Luxury Bole Penthouse',
        'slug' => 'luxury-bole-penthouse',
        'is_active' => true,
        'status' => 'active',
    ]);

    $this->get(route('properties.index'))
        ->assertOk()
        ->assertSee('Bole');

    $this->get(route('properties.show', $property->slug))
        ->assertOk()
        ->assertSee('Luxury Bole Penthouse');

    $this->get(route('stays.index'))
        ->assertOk();
});

test('unauthenticated guest cannot access buyer dashboard and is redirected to login', function () {
    $this->get(route('buyer.dashboard'))
        ->assertRedirect(route('login'));
});

test('authenticated guest role can access guest dashboard but cannot access admin dashboard', function () {
    $guest = User::factory()->create([
        'name' => 'Guest Visitor',
        'email' => 'guest@example.com',
        'role' => 'guest',
    ]);

    $this->actingAs($guest)->get(route('guest.dashboard'))
        ->assertOk()
        ->assertSee('Guest');

    $this->actingAs($guest)->get(route('dashboard'))
        ->assertForbidden();
});

// ── 2. BUYER DASHBOARD ACCESS & BOUNDARY CONTROLS ─────────────────────────────

test('authenticated buyer can access buyer dashboard with unified shell', function () {
    $buyer = User::factory()->create([
        'name' => 'Sara Buyer',
        'email' => 'sara@example.com',
        'role' => 'buyer',
    ]);

    $response = $this->actingAs($buyer)->get(route('buyer.dashboard'));

    $response->assertOk()
        ->assertSee('Sara Buyer')
        ->assertSee('Buyer Workspace')
        ->assertSee('Overview')
        ->assertSee('Collections')
        ->assertSee('Compare Matrix')
        ->assertSee('Saved Searches')
        ->assertSee('Inquiries')
        ->assertSee('Viewings')
        ->assertSee('My Offers')
        ->assertSee('Unit Reservations')
        ->assertSee('Document Vault')
        ->assertSee('Transaction Tracker')
        ->assertSee('Saved Properties');
});

test('admin cannot open individual buyer dashboard due to admin boundary', function () {
    $admin = User::factory()->create([
        'name' => 'System Admin',
        'email' => 'admin@example.com',
        'role' => 'admin',
    ]);

    $this->actingAs($admin)->get(route('buyer.dashboard'))
        ->assertForbidden();
});

test('suspended buyer cannot access buyer dashboard', function () {
    $buyer = User::factory()->create([
        'name' => 'Suspended Buyer',
        'email' => 'suspended@example.com',
        'role' => 'buyer',
        'status' => 'suspended',
    ]);

    $this->actingAs($buyer)->get(route('buyer.dashboard'))
        ->assertForbidden();
});

test('buyer with revoked browsing permissions gets 403 forbidden', function () {
    $buyer = User::factory()->create([
        'name' => 'Restricted Buyer',
        'email' => 'restricted@example.com',
        'role' => 'buyer',
    ]);

    // Deny all buyer access permissions
    $perms = Permission::whereIn('key', [
        'properties.browse', 'properties.search', 'searches.save',
        'viewings.create', 'viewings.book', 'viewings.view', 'offers.create', 'offers.view',
        'properties.view', 'property.view',
    ])->get();

    foreach ($perms as $perm) {
        UserPermissionOverride::updateOrCreate([
            'user_id' => $buyer->id,
            'permission_id' => $perm->id,
        ], ['granted' => false]);
    }

    $this->actingAs($buyer)->get(route('buyer.dashboard'))
        ->assertForbidden();
});

// ── 3. SAVED SEARCHES CRUD & BOUNDARY CONTROLS ────────────────────────────────

test('buyer can create, update, and delete saved searches with audit logging', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    // Create
    $response = $this->actingAs($buyer)->post(route('buyer.saved-searches.store'), [
        'title' => 'Bole 3-bed Condos',
        'city' => 'Addis Ababa',
        'property_category' => 'apartment',
        'max_price' => 12000000,
        'alert_frequency' => 'daily',
    ]);

    $response->assertRedirect();
    $savedSearch = SavedSearch::where('user_id', $buyer->id)->first();
    expect($savedSearch)->not->toBeNull()
        ->and($savedSearch->title)->toBe('Bole 3-bed Condos');

    $log = AuditLog::where('user_id', $buyer->id)->where('action', 'buyer.saved_search_created')->first();
    expect($log)->not->toBeNull();

    // Update
    $updateResponse = $this->actingAs($buyer)->put(route('buyer.saved-searches.update', $savedSearch), [
        'title' => 'Updated Bole Condos',
        'city' => 'Addis Ababa',
        'property_category' => 'apartment',
        'max_price' => 15000000,
        'alert_frequency' => 'instant',
    ]);

    $updateResponse->assertRedirect();
    expect($savedSearch->fresh()->title)->toBe('Updated Bole Condos')
        ->and($savedSearch->fresh()->alert_frequency)->toBe('instant');

    $updateLog = AuditLog::where('user_id', $buyer->id)->where('action', 'buyer.saved_search_updated')->first();
    expect($updateLog)->not->toBeNull();

    // Delete
    $deleteResponse = $this->actingAs($buyer)->delete(route('buyer.saved-searches.destroy', $savedSearch));
    $deleteResponse->assertRedirect();
    expect(SavedSearch::find($savedSearch->id))->toBeNull();

    $deleteLog = AuditLog::where('user_id', $buyer->id)->where('action', 'buyer.saved_search_deleted')->first();
    expect($deleteLog)->not->toBeNull();
});

test('buyer cannot update or delete another users saved search', function () {
    $buyer1 = User::factory()->create(['role' => 'buyer']);
    $buyer2 = User::factory()->create(['role' => 'buyer']);

    $search = $buyer1->savedSearches()->create([
        'title' => 'Buyer 1 Alert',
        'criteria' => ['city' => 'Addis Ababa'],
        'alert_frequency' => 'daily',
    ]);

    $this->actingAs($buyer2)->put(route('buyer.saved-searches.update', $search), [
        'title' => 'Hacked Alert',
        'alert_frequency' => 'daily',
    ])->assertForbidden();

    $this->actingAs($buyer2)->delete(route('buyer.saved-searches.destroy', $search))
        ->assertForbidden();
});

test('buyer with denied searches.save permission gets 403 on saved search actions', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $keys = ['searches.save', 'properties.save', 'properties.search'];
    foreach (Permission::whereIn('key', $keys)->get() as $perm) {
        UserPermissionOverride::updateOrCreate([
            'user_id' => $buyer->id,
            'permission_id' => $perm->id,
        ], ['granted' => false]);
    }

    $this->actingAs($buyer)->post(route('buyer.saved-searches.store'), [
        'title' => 'Denied Alert',
        'alert_frequency' => 'daily',
    ])->assertForbidden();
});

// ── 4. VIEWINGS RESCHEDULE & CANCEL CONTROLS ──────────────────────────────────

test('buyer can reschedule and cancel own viewing appointment with audit logging', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $property = Property::factory()->create();

    $viewing = PropertyViewing::factory()->create([
        'user_id' => $buyer->id,
        'buyer_email' => $buyer->email,
        'property_id' => $property->id,
        'viewing_date' => Carbon::tomorrow()->toDateString(),
        'viewing_time' => 'morning_09_11',
        'status' => 'confirmed',
    ]);

    // Reschedule
    $newDate = Carbon::today()->addDays(5)->toDateString();
    $rescheduleResponse = $this->actingAs($buyer)->put(route('buyer.viewings.reschedule', $viewing), [
        'viewing_date' => $newDate,
        'viewing_time' => 'afternoon_14_16',
        'buyer_notes' => 'Please move to Friday afternoon.',
    ]);

    $rescheduleResponse->assertRedirect();
    expect($viewing->fresh()->viewing_time)->toBe('afternoon_14_16')
        ->and($viewing->fresh()->status)->toBe('requested');

    $rescheduleLog = AuditLog::where('user_id', $buyer->id)->where('action', 'buyer.viewing_rescheduled')->first();
    expect($rescheduleLog)->not->toBeNull();

    // Cancel
    $cancelResponse = $this->actingAs($buyer)->put(route('buyer.viewings.cancel', $viewing));
    $cancelResponse->assertRedirect();
    expect($viewing->fresh()->status)->toBe('cancelled');

    $cancelLog = AuditLog::where('user_id', $buyer->id)->where('action', 'buyer.viewing_cancelled')->first();
    expect($cancelLog)->not->toBeNull();
});

test('buyer cannot reschedule or cancel another users viewing appointment', function () {
    $buyer1 = User::factory()->create(['role' => 'buyer']);
    $buyer2 = User::factory()->create(['role' => 'buyer']);
    $property = Property::factory()->create();

    $viewing = PropertyViewing::factory()->create([
        'user_id' => $buyer1->id,
        'buyer_email' => $buyer1->email,
        'property_id' => $property->id,
    ]);

    $this->actingAs($buyer2)->put(route('buyer.viewings.reschedule', $viewing), [
        'viewing_date' => Carbon::tomorrow()->toDateString(),
        'viewing_time' => 'morning_09_11',
    ])->assertForbidden();

    $this->actingAs($buyer2)->put(route('buyer.viewings.cancel', $viewing))
        ->assertForbidden();
});

// ── 5. OFFERS WITHDRAWAL & NEGOTIATION WORKFLOW ───────────────────────────────

test('buyer can withdraw own offer with audit logging and negotiation entry', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $property = Property::factory()->create(['price' => 5000000]);

    $offer = PropertyOffer::factory()->create([
        'user_id' => $buyer->id,
        'buyer_email' => $buyer->email,
        'property_id' => $property->id,
        'offered_price' => 4500000,
        'status' => 'submitted',
    ]);

    $response = $this->actingAs($buyer)->put(route('buyer.offers.withdraw', $offer), [
        'reason' => 'Purchased alternative property.',
    ]);

    $response->assertRedirect();
    expect($offer->fresh()->status)->toBe('withdrawn');

    // Negotiation entry should be recorded
    $negotiation = $offer->fresh()->negotiations()->latest()->first();
    expect($negotiation)->not->toBeNull()
        ->and($negotiation->sender_type)->toBe('buyer')
        ->and($negotiation->message)->toBe('Purchased alternative property.');

    $log = AuditLog::where('user_id', $buyer->id)->where('action', 'buyer.offer_withdrawn')->first();
    expect($log)->not->toBeNull();
});

test('buyer cannot withdraw another users offer', function () {
    $buyer1 = User::factory()->create(['role' => 'buyer']);
    $buyer2 = User::factory()->create(['role' => 'buyer']);
    $property = Property::factory()->create();

    $offer = PropertyOffer::factory()->create([
        'user_id' => $buyer1->id,
        'buyer_email' => $buyer1->email,
        'property_id' => $property->id,
    ]);

    $this->actingAs($buyer2)->put(route('buyer.offers.withdraw', $offer))
        ->assertForbidden();
});

// ── 6. UNIT RESERVATIONS CANCEL CONTROLS ──────────────────────────────────────

test('buyer can cancel own unit reservation with audit logging', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $project = Project::factory()->create();
    $unit = ProjectUnit::factory()->create([
        'project_id' => $project->id,
        'status' => 'reserved',
    ]);

    $reservation = UnitReservation::factory()->create([
        'user_id' => $buyer->id,
        'buyer_email' => $buyer->email,
        'project_id' => $project->id,
        'unit_id' => $unit->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($buyer)->put(route('buyer.reservations.cancel', $reservation));
    $response->assertRedirect();

    expect($reservation->fresh()->status)->toBe('cancelled')
        ->and($unit->fresh()->status)->toBe('available');

    $log = AuditLog::where('user_id', $buyer->id)->where('action', 'buyer.reservation_cancelled')->first();
    expect($log)->not->toBeNull();
});

test('buyer cannot cancel another users unit reservation', function () {
    $buyer1 = User::factory()->create(['role' => 'buyer']);
    $buyer2 = User::factory()->create(['role' => 'buyer']);

    $reservation = UnitReservation::factory()->create([
        'user_id' => $buyer1->id,
        'buyer_email' => $buyer1->email,
    ]);

    $this->actingAs($buyer2)->put(route('buyer.reservations.cancel', $reservation))
        ->assertForbidden();
});

// ── 7. DOCUMENT VAULT CRUD & BOUNDARY CONTROLS ────────────────────────────────

test('buyer can upload and delete own document with audit logging', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $file = UploadedFile::fake()->create('proof_of_funds.pdf', 500, 'application/pdf');

    $uploadResponse = $this->actingAs($buyer)->post(route('buyer.documents.upload'), [
        'title' => 'Commercial Bank of Ethiopia Statement',
        'document_type' => 'proof_of_funds',
        'document_file' => $file,
    ]);

    $uploadResponse->assertRedirect();
    $doc = PropertyDocument::where('user_id', $buyer->id)->first();
    expect($doc)->not->toBeNull()
        ->and($doc->title)->toBe('Commercial Bank of Ethiopia Statement')
        ->and($doc->access_level)->toBe('private')
        ->and(Storage::disk('public')->exists($doc->file_path))->toBeTrue();

    $uploadLog = AuditLog::where('user_id', $buyer->id)->where('action', 'buyer.document_uploaded')->first();
    expect($uploadLog)->not->toBeNull();

    // Delete
    $deleteResponse = $this->actingAs($buyer)->delete(route('buyer.documents.destroy', $doc));
    $deleteResponse->assertRedirect();

    expect(PropertyDocument::find($doc->id))->toBeNull()
        ->and(Storage::disk('public')->exists($doc->file_path))->toBeFalse();

    $deleteLog = AuditLog::where('user_id', $buyer->id)->where('action', 'buyer.document_deleted')->first();
    expect($deleteLog)->not->toBeNull();
});

test('buyer cannot delete another users document', function () {
    $buyer1 = User::factory()->create(['role' => 'buyer']);
    $buyer2 = User::factory()->create(['role' => 'buyer']);

    $doc = PropertyDocument::create([
        'user_id' => $buyer1->id,
        'property_id' => null,
        'title' => 'Buyer 1 Passport',
        'document_type' => 'national_id',
        'file_path' => 'buyer-documents/passport.pdf',
        'file_name' => 'passport.pdf',
        'access_level' => 'private',
        'status' => 'verified',
    ]);

    $this->actingAs($buyer2)->delete(route('buyer.documents.destroy', $doc))
        ->assertForbidden();
});

// ── 8. INQUIRIES & MESSAGING CONTROLS ─────────────────────────────────────────

test('buyer can reply to inquiry thread with audit logging', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $property = Property::factory()->create();

    $inquiry = Inquiry::create([
        'user_id' => $buyer->id,
        'email' => $buyer->email,
        'name' => $buyer->name,
        'property_id' => $property->id,
        'subject' => 'Is this unit available for purchase?',
        'message' => 'Initial inquiry about the price and terms.',
        'status' => 'new',
    ]);

    $response = $this->actingAs($buyer)->post(route('buyer.inquiries.reply', $inquiry), [
        'message' => 'Can we do an inspection this Thursday?',
    ]);

    $response->assertRedirect();
    expect($inquiry->fresh()->status)->toBe('contacted')
        ->and($inquiry->fresh()->message)->toContain('Can we do an inspection this Thursday?');

    $log = AuditLog::where('user_id', $buyer->id)->where('action', 'buyer.inquiry_reply_sent')->first();
    expect($log)->not->toBeNull();
});

test('buyer cannot reply to another users inquiry', function () {
    $buyer1 = User::factory()->create(['role' => 'buyer']);
    $buyer2 = User::factory()->create(['role' => 'buyer']);

    $inquiry = Inquiry::create([
        'user_id' => $buyer1->id,
        'email' => $buyer1->email,
        'name' => $buyer1->name,
        'subject' => 'Buyer 1 Question',
        'message' => 'Private message.',
        'status' => 'new',
    ]);

    $this->actingAs($buyer2)->post(route('buyer.inquiries.reply', $inquiry), [
        'message' => 'Intruder reply',
    ])->assertForbidden();
});

// ── 9. BUTTON & LINK CRAWL VERIFICATION ───────────────────────────────────────

test('buyer dashboard renders with valid navigation anchors, buttons, and form targets', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $response = $this->actingAs($buyer)->get(route('buyer.dashboard'));
    $response->assertOk();

    $content = $response->getContent();

    // Verify all module section IDs exist in the rendered HTML
    $sections = [
        'overview', 'collections', 'compare', 'saved-searches',
        'inquiries', 'viewings', 'offers', 'reservations',
        'documents', 'deals', 'saved-properties',
    ];

    foreach ($sections as $section) {
        expect($content)->toContain("id=\"{$section}\"");
    }

    // Verify all action routes exist and are referenced in form actions
    expect($content)->toContain(route('collections.store'))
        ->and($content)->toContain(route('buyer.saved-searches.store'))
        ->and($content)->toContain(route('buyer.documents.upload'));
});
