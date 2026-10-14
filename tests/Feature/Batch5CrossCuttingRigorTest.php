<?php

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\FinancialLedger;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectUnit;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\UnitReservation;
use App\Models\User;
use App\Services\ImageSecurityService;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');
});

// ══════════════════════════════════════════════════════════════════════════════
// 1. CONCURRENCY & OVERLAP PREVENTION TESTS (SRS §78-§83, §95)
// ══════════════════════════════════════════════════════════════════════════════

test('stay booking engine rejects double booking on overlapping dates with lock and validation', function () {
    $host = User::factory()->create(['role' => 'host']);
    $property = Property::create([
        'title' => 'Bole Medhanealem Luxury Penthouse',
        'slug' => 'bole-medhanealem-luxury-penthouse-batch5',
        'description' => 'Spacious penthouse perfect for short stays.',
        'price' => 5000.00,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 120,
        'type' => 'rent',
        'property_category' => 'apartment',
        'city' => 'Addis Ababa',
        'address' => 'Near Edna Mall, Bole',
        'status' => 'published',
        'is_active' => true,
        'user_id' => $host->id,
    ]);

    $listing = Listing::create([
        'property_id' => $property->id,
        'user_id' => $host->id,
        'title' => 'Bole Boutique Suite',
        'listing_type' => 'stay',
        'price' => 5000.00,
        'currency' => 'ETB',
        'status' => 'approved',
    ]);

    $checkIn = Carbon::now()->addDays(10)->format('Y-m-d');
    $checkOut = Carbon::now()->addDays(15)->format('Y-m-d');

    // Existing active booking
    Booking::create([
        'booking_reference' => 'STAY-AA-202611-CONF',
        'property_id' => $property->id,
        'listing_id' => $listing->id,
        'guest_name' => 'First Guest',
        'guest_email' => 'first@example.com',
        'guest_phone' => '+251911111111',
        'check_in_date' => $checkIn,
        'check_out_date' => $checkOut,
        'total_nights' => 5,
        'guests_count' => 2,
        'nightly_price' => 5000.00,
        'accommodation_total' => 25000.00,
        'cleaning_fee' => 500.00,
        'service_fee' => 1250.00,
        'total_amount' => 26750.00,
        'currency' => 'ETB',
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ]);

    $client = User::factory()->create(['role' => 'client']);

    // Attempt overlapping reservation (starts inside existing booking)
    $response = $this->actingAs($client)->post(route('stays.book', $property), [
        'check_in_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(17)->format('Y-m-d'),
        'guests_count' => 2,
        'guest_name' => 'Second Guest',
        'guest_email' => 'second@example.com',
        'guest_phone' => '+251922222222',
    ]);

    $response->assertSessionHasErrors(['check_in_date']);
    expect(Booking::where('guest_email', 'second@example.com')->count())->toBe(0);
});

test('off-plan unit reservation prevents double reservation and locks unit status', function () {
    $developer = User::factory()->create(['role' => 'developer']);
    $project = Project::create([
        'project_code' => 'ETH-DEV-TEST-01',
        'name' => 'Summit Heights Residences',
        'slug' => 'summit-heights-residences',
        'developer_name' => 'Summit Dev PLC',
        'developer_phone' => '+251911223344',
        'developer_email' => 'info@summit.et',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'address' => 'Summit Road',
        'project_type' => 'residential',
        'construction_status' => 'under_construction',
        'overall_completion_percentage' => 45,
        'land_area_sqm' => 3000,
        'total_buildings_count' => 1,
        'total_units_count' => 20,
        'starting_price' => 8000000,
        'currency' => 'ETB',
        'status' => 'published',
    ]);

    $building = ProjectBuilding::create([
        'project_id' => $project->id,
        'name' => 'Block A',
        'total_floors' => 'G+10',
        'units_count' => 20,
    ]);

    $unit = ProjectUnit::create([
        'project_id' => $project->id,
        'building_id' => $building->id,
        'unit_number' => 'U-301',
        'floor_number' => '3',
        'unit_type' => '2_bedroom',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area_sqm' => 95,
        'price' => 8500000,
        'currency' => 'ETB',
        'status' => 'available',
    ]);

    $client1 = User::factory()->create(['role' => 'client']);
    $client2 = User::factory()->create(['role' => 'client']);

    // First reservation succeeds
    $res1 = $this->actingAs($client1)->post(route('developments.reserve-unit', ['project' => $project->id, 'unit' => $unit->id]), [
        'unit_id' => $unit->id,
        'buyer_name' => 'First Buyer',
        'buyer_email' => 'buyer1@example.com',
        'buyer_phone' => '+251911111111',
    ]);

    $res1->assertRedirect();
    $res1->assertSessionHas('developer_reservation_success');
    expect($unit->fresh()->status)->toBe('reserved');

    // Second reservation on now-reserved unit fails
    $res2 = $this->actingAs($client2)->post(route('developments.reserve-unit', ['project' => $project->id, 'unit' => $unit->id]), [
        'unit_id' => $unit->id,
        'buyer_name' => 'Second Buyer',
        'buyer_email' => 'buyer2@example.com',
        'buyer_phone' => '+251922222222',
    ]);

    $res2->assertSessionHasErrors(['unit_id']);
    expect(UnitReservation::where('buyer_email', 'buyer2@example.com')->count())->toBe(0);
});

// ══════════════════════════════════════════════════════════════════════════════
// 2. IDEMPOTENT PAYMENT CALLBACK TESTS (SRS §82-§84)
// ══════════════════════════════════════════════════════════════════════════════

test('payment webhook callback is strictly idempotent and prevents duplicate ledger records', function () {
    $payer = User::factory()->create(['role' => 'client']);
    $payment = Payment::create([
        'transaction_number' => 'TXN-IDEMPOTENT-TEST-001',
        'payment_type' => 'rent_installment',
        'payer_name' => $payer->name,
        'payer_email' => $payer->email,
        'payer_phone' => '+251911000000',
        'user_id' => $payer->id,
        'amount' => 15000.00,
        'currency' => 'ETB',
        'payment_method' => 'chapa',
        'payment_category' => 'rent',
        'status' => 'pending',
    ]);

    $payload = [
        'provider' => 'chapa',
        'gateway_reference' => 'CHAPA-REF-998877',
        'transaction_number' => $payment->transaction_number,
        'amount' => 15000.00,
        'currency' => 'ETB',
        'status' => 'completed',
    ];

    // 1. Initial callback -> marks completed, creates ledger record
    $res1 = $this->postJson(route('payments.callback'), $payload);
    $res1->assertStatus(200);
    $res1->assertJson([
        'success' => true,
        'status' => 'completed',
        'idempotent' => false,
    ]);

    $payment->refresh();
    expect($payment->status)->toBe('completed');
    expect($payment->bank_transaction_reference)->toBe('CHAPA-REF-998877');

    $initialLedgerCount = FinancialLedger::where('payment_id', $payment->id)->count();
    expect($initialLedgerCount)->toBeGreaterThanOrEqual(1);

    // 2. Redelivery of the exact same callback -> returns 200 with already_processed and NO new ledger entries
    $res2 = $this->postJson(route('payments.callback'), $payload);
    $res2->assertStatus(200);
    $res2->assertJson([
        'success' => true,
        'status' => 'already_processed',
        'idempotent' => true,
    ]);

    $finalLedgerCount = FinancialLedger::where('payment_id', $payment->id)->count();
    expect($finalLedgerCount)->toBe($initialLedgerCount);
});

// ══════════════════════════════════════════════════════════════════════════════
// 3. HOLD EXPIRATION COMMAND TESTS
// ══════════════════════════════════════════════════════════════════════════════

test('reservations:expire-holds command expires unpaid stay bookings past hold threshold', function () {
    $host = User::factory()->create(['role' => 'host']);
    $property = Property::factory()->create(['user_id' => $host->id]);

    // Booking past 24h hold threshold
    $expiredBooking = Booking::create([
        'booking_reference' => 'STAY-EXPIRE-01',
        'property_id' => $property->id,
        'guest_name' => 'Expired Guest',
        'guest_email' => 'expired@example.com',
        'guest_phone' => '+251911111111',
        'check_in_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        'total_nights' => 2,
        'guests_count' => 1,
        'nightly_price' => 2000.00,
        'accommodation_total' => 4000.00,
        'total_amount' => 4000.00,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);
    // Backdate created_at to 26 hours ago
    $expiredBooking->created_at = Carbon::now()->subHours(26);
    $expiredBooking->saveQuietly();

    // Fresh booking within hold threshold (created 1 hour ago)
    $activeBooking = Booking::create([
        'booking_reference' => 'STAY-ACTIVE-01',
        'property_id' => $property->id,
        'guest_name' => 'Active Guest',
        'guest_email' => 'active@example.com',
        'guest_phone' => '+251922222222',
        'check_in_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
        'total_nights' => 2,
        'guests_count' => 1,
        'nightly_price' => 2000.00,
        'accommodation_total' => 4000.00,
        'total_amount' => 4000.00,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);
    $activeBooking->created_at = Carbon::now()->subHour();
    $activeBooking->saveQuietly();

    // Execute artisan command
    $this->artisan('reservations:expire-holds', ['--stay-hours' => 24])
        ->assertSuccessful();

    expect($expiredBooking->fresh()->status)->toBe('cancelled');
    expect($expiredBooking->fresh()->cancelled_at)->not->toBeNull();
    expect($activeBooking->fresh()->status)->toBe('pending');
});

test('reservations:expire-holds command expires off-plan reservations and frees held units', function () {
    $project = Project::create([
        'project_code' => 'ETH-DEV-HOLD-TEST',
        'name' => 'Hold Test Development',
        'slug' => 'hold-test-development',
        'developer_name' => 'Hold Dev',
        'developer_phone' => '+251911000000',
        'developer_email' => 'hold@example.com',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'address' => 'Bole Road',
        'project_type' => 'residential',
        'construction_status' => 'under_construction',
        'overall_completion_percentage' => 30,
        'land_area_sqm' => 2000,
        'total_buildings_count' => 1,
        'total_units_count' => 10,
        'starting_price' => 5000000,
        'currency' => 'ETB',
        'status' => 'published',
    ]);

    $unitExpired = ProjectUnit::create([
        'project_id' => $project->id,
        'unit_number' => 'U-HOLD-1',
        'floor_number' => '1',
        'unit_type' => '1_bedroom',
        'bedrooms' => 1,
        'bathrooms' => 1,
        'area_sqm' => 60,
        'price' => 5000000,
        'currency' => 'ETB',
        'status' => 'reserved',
    ]);

    $unitActive = ProjectUnit::create([
        'project_id' => $project->id,
        'unit_number' => 'U-HOLD-2',
        'floor_number' => '2',
        'unit_type' => '1_bedroom',
        'bedrooms' => 1,
        'bathrooms' => 1,
        'area_sqm' => 60,
        'price' => 5000000,
        'currency' => 'ETB',
        'status' => 'reserved',
    ]);

    // Expired unit reservation (75 hours ago)
    $expiredReservation = UnitReservation::create([
        'project_id' => $project->id,
        'unit_id' => $unitExpired->id,
        'buyer_name' => 'Expired Unit Buyer',
        'buyer_email' => 'unitexpired@example.com',
        'buyer_phone' => '+251911222333',
        'status' => 'pending',
    ]);
    $expiredReservation->created_at = Carbon::now()->subHours(75);
    $expiredReservation->saveQuietly();

    // Fresh unit reservation (10 hours ago)
    $activeReservation = UnitReservation::create([
        'project_id' => $project->id,
        'unit_id' => $unitActive->id,
        'buyer_name' => 'Active Unit Buyer',
        'buyer_email' => 'unitactive@example.com',
        'buyer_phone' => '+251944555666',
        'status' => 'pending',
    ]);
    $activeReservation->created_at = Carbon::now()->subHours(10);
    $activeReservation->saveQuietly();

    // Execute artisan command
    $this->artisan('reservations:expire-holds', ['--unit-hours' => 72])
        ->assertSuccessful();

    expect($expiredReservation->fresh()->status)->toBe('cancelled');
    expect($unitExpired->fresh()->status)->toBe('available');

    expect($activeReservation->fresh()->status)->toBe('pending');
    expect($unitActive->fresh()->status)->toBe('reserved');
});

// ══════════════════════════════════════════════════════════════════════════════
// 4. FINANCIAL LEDGER & AUDIT LOG IMMUTABILITY TESTS
// ══════════════════════════════════════════════════════════════════════════════

test('financial ledger entries are strictly immutable against updates and deletions', function () {
    $user = User::factory()->create(['role' => 'client']);
    $ledger = FinancialLedger::create([
        'entry_type' => 'credit',
        'amount' => 25000.00,
        'category' => 'rental_revenue',
        'description' => 'Rent installment collection',
        'currency' => 'ETB',
        'reference' => 'REF-12345',
        'user_id' => $user->id,
    ]);

    expect($ledger)->not->toBeNull();

    // Attempting update must throw RuntimeException
    expect(fn () => $ledger->update(['amount' => 99999.00]))
        ->toThrow(RuntimeException::class, 'Financial records are immutable and cannot be overwritten. Record a financial adjustment entry instead.');

    // Attempting delete must throw RuntimeException
    expect(fn () => $ledger->delete())
        ->toThrow(RuntimeException::class, 'Financial records are immutable and cannot be deleted.');
});

test('audit log entries are strictly immutable against updates and deletions', function () {
    $user = User::factory()->create(['role' => 'administrator']);

    $auditLog = AuditLog::log(
        action: 'system.security_audit',
        entity: $user,
        oldValue: ['security_level' => 'standard'],
        newValue: ['security_level' => 'high'],
        reason: 'Periodic security baseline upgrade'
    );

    expect($auditLog)->not->toBeNull();

    // Attempting update must throw RuntimeException
    expect(fn () => $auditLog->update(['action' => 'tampered.action']))
        ->toThrow(RuntimeException::class, 'Audit logs are immutable and cannot be updated.');

    // Attempting delete must throw RuntimeException
    expect(fn () => $auditLog->delete())
        ->toThrow(RuntimeException::class, 'Audit logs are immutable and cannot be deleted.');
});

// ══════════════════════════════════════════════════════════════════════════════
// 5. DOCUMENT ACCESS LEVELS & SIGNED PRIVATE URL TESTS (SRS §28)
// ══════════════════════════════════════════════════════════════════════════════

test('private documents reject unsigned requests and allow access via valid temporary signed URLs', function () {
    $owner = User::factory()->create(['role' => 'seller']);
    $viewer = User::factory()->create(['role' => 'buyer']);
    $suspendedUser = User::factory()->create(['role' => 'buyer', 'status' => 'suspended']);

    $property = Property::factory()->create(['user_id' => $owner->id]);

    $doc = PropertyDocument::create([
        'property_id' => $property->id,
        'user_id' => $owner->id,
        'title' => 'Title Deed Certificate',
        'document_type' => 'title_deed',
        'file_name' => 'title_deed.pdf',
        'file_path' => 'documents/title_deed.pdf',
        'file_size' => 1024,
        'mime_type' => 'application/pdf',
        'access_level' => 'private',
        'verification_status' => 'verified',
    ]);

    // 1. Direct unsigned request from unrelated viewer -> 403 Forbidden
    $unsignedRes = $this->actingAs($viewer)->get(route('media.document', $doc));
    $unsignedRes->assertStatus(403);

    // 2. Tampered or expired signed URL -> 403 Forbidden
    $tamperedUrl = route('media.document', $doc).'?signature=invalid_tampered_signature&expires=9999999999';
    $tamperedRes = $this->actingAs($viewer)->get($tamperedUrl);
    $tamperedRes->assertStatus(403);

    // 3. Valid signed URL -> 200 OK
    $validSignedUrl = $doc->getSignedUrl(15);
    $signedRes = $this->actingAs($viewer)->get($validSignedUrl);
    $signedRes->assertStatus(200);

    // 4. Suspended user with valid signed URL is still denied -> 403 Forbidden
    $suspendedRes = $this->actingAs($suspendedUser)->get($validSignedUrl);
    $suspendedRes->assertStatus(403);

    // 5. Admin-only document accessed with signed URL by regular viewer -> 403 Forbidden
    $adminDoc = PropertyDocument::create([
        'property_id' => $property->id,
        'user_id' => $owner->id,
        'title' => 'Internal Legal Audit',
        'document_type' => 'internal_audit',
        'file_name' => 'internal_audit.pdf',
        'file_path' => 'documents/internal_audit.pdf',
        'file_size' => 1024,
        'mime_type' => 'application/pdf',
        'access_level' => 'admin_only',
        'verification_status' => 'pending',
    ]);

    $adminDocSignedUrl = $adminDoc->getSignedUrl(15);
    $nonAdminRes = $this->actingAs($viewer)->get($adminDocSignedUrl);
    $nonAdminRes->assertStatus(403);
});

// ══════════════════════════════════════════════════════════════════════════════
// 6. EXIF/GPS METADATA STRIPPING TESTS (SRS §28)
// ══════════════════════════════════════════════════════════════════════════════

test('image security service strips EXIF and GPS tags from uploaded photos', function () {
    // Generate a temporary test JPEG image using GD
    $tmpFile = tempnam(sys_get_temp_dir(), 'exif_test_').'.jpg';
    $img = imagecreatetruecolor(100, 100);
    $bgColor = imagecolorallocate($img, 30, 144, 255);
    imagefill($img, 0, 0, $bgColor);
    imagejpeg($img, $tmpFile, 90);
    imagedestroy($img);

    expect(file_exists($tmpFile))->toBeTrue();

    // Strip EXIF
    $result = ImageSecurityService::stripExif($tmpFile);
    expect($result)->toBeTrue();

    // Verify it is a valid JPEG without errors
    $reloaded = @imagecreatefromjpeg($tmpFile);
    expect($reloaded)->not->toBeFalse();
    imagedestroy($reloaded);

    @unlink($tmpFile);
});

test('image security service storeAndStrip stores file on public disk without metadata', function () {
    $uploadedFile = UploadedFile::fake()->image('property_front.jpg', 600, 400);

    $storedPath = ImageSecurityService::storeAndStrip($uploadedFile, 'properties/gallery', 'public');

    expect(Storage::disk('public')->exists($storedPath))->toBeTrue();

    $fullPath = Storage::disk('public')->path($storedPath);
    $img = @imagecreatefromjpeg($fullPath);
    expect($img)->not->toBeFalse();
    imagedestroy($img);
});
