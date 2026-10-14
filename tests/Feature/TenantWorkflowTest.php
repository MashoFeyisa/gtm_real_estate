<?php

use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Permission;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyOffer;
use App\Models\RentSchedule;
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

// ── 1. TENANT ACCESS & BOUNDARY CONTROLS ──────────────────────────────────────

test('authenticated tenant can access tenant dashboard with unified shell', function () {
    $tenant = User::factory()->create([
        'name' => 'Tigist Tenant',
        'email' => 'tigist@example.com',
        'role' => 'tenant',
    ]);

    $response = $this->actingAs($tenant)->get(route('tenant.dashboard'));

    $response->assertOk()
        ->assertSee('Tigist Tenant')
        ->assertSee('Tenant Workspace')
        ->assertSee('Overview')
        ->assertSee('Applications')
        ->assertSee('My Leases')
        ->assertSee('Rent Schedule')
        ->assertSee('Maintenance')
        ->assertSee('Landlord Messages')
        ->assertSee('Tenancy Vault')
        ->assertSee('Find Rentals');
});

test('admin cannot open individual tenant dashboard due to boundary enforcement', function () {
    $admin = User::factory()->create([
        'name' => 'Platform Admin',
        'email' => 'admin@example.com',
        'role' => 'admin',
    ]);

    $this->actingAs($admin)->get(route('tenant.dashboard'))
        ->assertForbidden();
});

test('suspended tenant cannot access tenant dashboard', function () {
    $tenant = User::factory()->create([
        'name' => 'Suspended Tenant',
        'email' => 'suspended@example.com',
        'role' => 'tenant',
        'status' => 'suspended',
    ]);

    $this->actingAs($tenant)->get(route('tenant.dashboard'))
        ->assertForbidden();
});

test('tenant with revoked permissions gets 403 forbidden', function () {
    $tenant = User::factory()->create([
        'name' => 'Restricted Tenant',
        'email' => 'restricted@example.com',
        'role' => 'tenant',
    ]);

    $keys = [
        'agreements.view', 'rent.view', 'leases.view', 'leases.sign',
        'rent.pay', 'applications.view', 'applications.create',
        'maintenance.view', 'maintenance.create', 'documents.view',
    ];

    $perms = Permission::whereIn('key', $keys)->get();
    foreach ($perms as $perm) {
        UserPermissionOverride::updateOrCreate([
            'user_id' => $tenant->id,
            'permission_id' => $perm->id,
        ], ['granted' => false]);
    }

    $this->actingAs($tenant)->get(route('tenant.dashboard'))
        ->assertForbidden();
});

// ── 2. RENTAL APPLICATIONS CRUD & BOUNDARY CONTROLS ───────────────────────────

test('tenant can submit, edit, and withdraw rental application with audit logging', function () {
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create(['price' => 40000, 'type' => 'rent']);

    $file = UploadedFile::fake()->create('employment_slip.pdf', 300, 'application/pdf');

    // 1. Submit Application
    $response = $this->actingAs($tenant)->post(route('tenant.applications.store'), [
        'property_id' => $property->id,
        'offered_price' => 38000,
        'deposit_proposed' => 76000,
        'valid_until' => Carbon::tomorrow()->toDateString(),
        'message' => 'Looking for a 1-year residential lease.',
        'supporting_document' => $file,
    ]);

    $response->assertRedirect();
    $app = PropertyOffer::where('user_id', $tenant->id)->where('offer_type', 'rental')->first();
    expect($app)->not->toBeNull()
        ->and((float) $app->offered_price)->toBe(38000.0)
        ->and($app->supporting_document_path)->not->toBeNull()
        ->and(Storage::disk('public')->exists($app->supporting_document_path))->toBeTrue();

    $log = AuditLog::where('user_id', $tenant->id)->where('action', 'tenant.application_created')->first();
    expect($log)->not->toBeNull();

    // 2. Edit Application
    $editResponse = $this->actingAs($tenant)->put(route('tenant.applications.update', $app), [
        'offered_price' => 39000,
        'deposit_proposed' => 78000,
        'message' => 'Revised offer with 2 months advance deposit.',
    ]);

    $editResponse->assertRedirect();
    expect((float) $app->fresh()->offered_price)->toBe(39000.0)
        ->and($app->fresh()->message)->toContain('Revised offer');

    $editLog = AuditLog::where('user_id', $tenant->id)->where('action', 'tenant.application_updated')->first();
    expect($editLog)->not->toBeNull();

    // 3. Withdraw Application
    $withdrawResponse = $this->actingAs($tenant)->put(route('tenant.applications.withdraw', $app));
    $withdrawResponse->assertRedirect();
    expect($app->fresh()->status)->toBe('withdrawn');

    $withdrawLog = AuditLog::where('user_id', $tenant->id)->where('action', 'tenant.application_withdrawn')->first();
    expect($withdrawLog)->not->toBeNull();
});

test('tenant cannot edit or withdraw another users rental application', function () {
    $tenant1 = User::factory()->create(['role' => 'tenant']);
    $tenant2 = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create(['type' => 'rent']);

    $app = PropertyOffer::create([
        'user_id' => $tenant1->id,
        'buyer_email' => $tenant1->email,
        'buyer_name' => $tenant1->name,
        'buyer_phone' => '+251911000000',
        'property_id' => $property->id,
        'offer_type' => 'rental',
        'original_price' => $property->price,
        'offered_price' => 30000,
        'status' => 'submitted',
    ]);

    $this->actingAs($tenant2)->put(route('tenant.applications.update', $app), [
        'offered_price' => 20000,
    ])->assertForbidden();

    $this->actingAs($tenant2)->put(route('tenant.applications.withdraw', $app))
        ->assertForbidden();
});

// ── 3. LEASE AGREEMENT DIGITAL SIGNING ────────────────────────────────────────

test('tenant can digitally sign and activate lease agreement with audit logging', function () {
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create(['type' => 'rent']);

    $lease = Lease::create([
        'property_id' => $property->id,
        'tenant_user_id' => $tenant->id,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'tenant_phone' => '+251911000000',
        'start_date' => Carbon::today(),
        'end_date' => Carbon::today()->addYear(),
        'duration_months' => 12,
        'monthly_rent' => 25000,
        'security_deposit' => 50000,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($tenant)->post(route('tenant.leases.sign', $lease), [
        'tenant_signature' => 'Tigist Tenant Signature',
        'agree_terms' => '1',
    ]);

    $response->assertRedirect();
    expect($lease->fresh()->status)->toBe('active')
        ->and($lease->fresh()->tenant_signature)->toBe('Tigist Tenant Signature')
        ->and($lease->fresh()->signed_at)->not->toBeNull()
        ->and($lease->fresh()->schedules()->count())->toBeGreaterThan(0);

    $log = AuditLog::where('user_id', $tenant->id)->where('action', 'tenant.lease_signed')->first();
    expect($log)->not->toBeNull();
});

test('tenant cannot sign another tenants lease agreement', function () {
    $tenant1 = User::factory()->create(['role' => 'tenant']);
    $tenant2 = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create();

    $lease = Lease::create([
        'property_id' => $property->id,
        'tenant_user_id' => $tenant1->id,
        'tenant_name' => $tenant1->name,
        'tenant_email' => $tenant1->email,
        'tenant_phone' => '+251911000000',
        'start_date' => Carbon::today(),
        'end_date' => Carbon::today()->addYear(),
        'duration_months' => 12,
        'monthly_rent' => 20000,
        'status' => 'draft',
    ]);

    $this->actingAs($tenant2)->post(route('tenant.leases.sign', $lease), [
        'tenant_signature' => 'Intruder',
        'agree_terms' => '1',
    ])->assertForbidden();
});

// ── 4. RENT SCHEDULE & RECEIPTS ───────────────────────────────────────────────

test('tenant can view receipt voucher for rent installment', function () {
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create();

    $lease = Lease::create([
        'property_id' => $property->id,
        'tenant_user_id' => $tenant->id,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'tenant_phone' => '+251911000000',
        'start_date' => Carbon::today(),
        'end_date' => Carbon::today()->addYear(),
        'duration_months' => 12,
        'monthly_rent' => 20000,
        'status' => 'active',
    ]);

    $schedule = RentSchedule::create([
        'lease_id' => $lease->id,
        'installment_number' => 1,
        'period_label' => 'January 2026',
        'due_date' => Carbon::today(),
        'amount_due' => 20000,
        'amount_paid' => 20000,
        'status' => 'paid',
        'paid_at' => Carbon::now(),
        'payment_method' => 'telebirr',
    ]);

    $response = $this->actingAs($tenant)->get(route('tenant.rent.receipt', $schedule));
    $response->assertOk()
        ->assertSee('Rent Installment Receipt')
        ->assertSee('January 2026')
        ->assertSee('20,000.00');
});

test('tenant cannot view receipt voucher for another tenants schedule', function () {
    $tenant1 = User::factory()->create(['role' => 'tenant']);
    $tenant2 = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create();

    $lease = Lease::create([
        'property_id' => $property->id,
        'tenant_user_id' => $tenant1->id,
        'tenant_name' => $tenant1->name,
        'tenant_email' => $tenant1->email,
        'tenant_phone' => '+251911000000',
        'start_date' => Carbon::today(),
        'end_date' => Carbon::today()->addYear(),
        'duration_months' => 12,
        'monthly_rent' => 20000,
        'status' => 'active',
    ]);

    $schedule = RentSchedule::create([
        'lease_id' => $lease->id,
        'installment_number' => 1,
        'period_label' => 'January 2026',
        'due_date' => Carbon::today(),
        'amount_due' => 20000,
        'status' => 'paid',
    ]);

    $this->actingAs($tenant2)->get(route('tenant.rent.receipt', $schedule))
        ->assertForbidden();
});

// ── 5. MAINTENANCE TICKETS PHOTO UPLOAD, UPDATE & CLOSE ───────────────────────

test('tenant can submit maintenance ticket with photo, update it, and close it', function () {
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create();

    $photo = UploadedFile::fake()->image('pipe_leak.jpg');

    // 1. Submit Maintenance
    $response = $this->actingAs($tenant)->post(route('tenant.maintenance.store'), [
        'property_id' => $property->id,
        'title' => 'Kitchen Sink Pipe Burst',
        'category' => 'plumbing',
        'priority' => 'emergency',
        'description' => 'Water leaking heavily under the kitchen sink.',
        'photo' => $photo,
    ]);

    $response->assertRedirect();
    $ticket = MaintenanceTicket::where('user_id', $tenant->id)->first();
    expect($ticket)->not->toBeNull()
        ->and($ticket->title)->toBe('Kitchen Sink Pipe Burst')
        ->and($ticket->photo_path)->not->toBeNull()
        ->and(Storage::disk('public')->exists($ticket->photo_path))->toBeTrue();

    $createLog = AuditLog::where('user_id', $tenant->id)->where('action', 'tenant.maintenance_submitted')->first();
    expect($createLog)->not->toBeNull();

    // 2. Update Maintenance
    $updateResponse = $this->actingAs($tenant)->put(route('tenant.maintenance.update', $ticket), [
        'additional_notes' => 'Turned off main valve. Awaiting maintenance dispatch.',
    ]);

    $updateResponse->assertRedirect();
    expect($ticket->fresh()->description)->toContain('Turned off main valve');

    $updateLog = AuditLog::where('user_id', $tenant->id)->where('action', 'tenant.maintenance_updated')->first();
    expect($updateLog)->not->toBeNull();

    // 3. Close Maintenance
    $closeResponse = $this->actingAs($tenant)->put(route('tenant.maintenance.close', $ticket));
    $closeResponse->assertRedirect();
    expect($ticket->fresh()->status)->toBe('closed')
        ->and($ticket->fresh()->resolved_at)->not->toBeNull();

    $closeLog = AuditLog::where('user_id', $tenant->id)->where('action', 'tenant.maintenance_closed')->first();
    expect($closeLog)->not->toBeNull();
});

test('tenant cannot update or close another users maintenance ticket', function () {
    $tenant1 = User::factory()->create(['role' => 'tenant']);
    $tenant2 = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create();

    $ticket = MaintenanceTicket::create([
        'user_id' => $tenant1->id,
        'property_id' => $property->id,
        'title' => 'Tenant 1 Issue',
        'description' => 'Fix heater',
        'priority' => 'normal',
        'status' => 'open',
    ]);

    $this->actingAs($tenant2)->put(route('tenant.maintenance.update', $ticket), [
        'additional_notes' => 'Intruder note',
    ])->assertForbidden();

    $this->actingAs($tenant2)->put(route('tenant.maintenance.close', $ticket))
        ->assertForbidden();
});

// ── 6. DIRECT MESSAGES & INQUIRIES ────────────────────────────────────────────

test('tenant can reply to landlord direct messages with audit logging', function () {
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create();

    $inquiry = Inquiry::create([
        'user_id' => $tenant->id,
        'email' => $tenant->email,
        'name' => $tenant->name,
        'property_id' => $property->id,
        'subject' => 'Lease Extension Inquiry',
        'message' => 'Can we discuss extending the lease for 6 more months?',
        'status' => 'new',
    ]);

    $response = $this->actingAs($tenant)->post(route('tenant.messages.reply', $inquiry), [
        'message' => 'Yes, our current agreement expires next month.',
    ]);

    $response->assertRedirect();
    expect($inquiry->fresh()->status)->toBe('contacted')
        ->and($inquiry->fresh()->message)->toContain('Yes, our current agreement expires next month.');

    $log = AuditLog::where('user_id', $tenant->id)->where('action', 'tenant.message_sent')->first();
    expect($log)->not->toBeNull();
});

test('tenant cannot reply to another users message thread', function () {
    $tenant1 = User::factory()->create(['role' => 'tenant']);
    $tenant2 = User::factory()->create(['role' => 'tenant']);

    $inquiry = Inquiry::create([
        'user_id' => $tenant1->id,
        'email' => $tenant1->email,
        'name' => $tenant1->name,
        'subject' => 'Private Tenant 1 Discussion',
        'message' => 'Personal message.',
        'status' => 'new',
    ]);

    $this->actingAs($tenant2)->post(route('tenant.messages.reply', $inquiry), [
        'message' => 'Hijacked reply',
    ])->assertForbidden();
});

// ── 7. TENANCY DOCUMENTS VAULT ────────────────────────────────────────────────

test('tenant can upload and delete tenancy document with audit logging', function () {
    $tenant = User::factory()->create(['role' => 'tenant']);

    $file = UploadedFile::fake()->create('guarantor_letter.pdf', 400, 'application/pdf');

    $uploadResponse = $this->actingAs($tenant)->post(route('tenant.documents.upload'), [
        'title' => 'Employer Guarantee Letter',
        'document_type' => 'lease_guarantor',
        'document_file' => $file,
    ]);

    $uploadResponse->assertRedirect();
    $doc = PropertyDocument::where('user_id', $tenant->id)->first();
    expect($doc)->not->toBeNull()
        ->and($doc->title)->toBe('Employer Guarantee Letter')
        ->and($doc->access_level)->toBe('private')
        ->and(Storage::disk('public')->exists($doc->file_path))->toBeTrue();

    $uploadLog = AuditLog::where('user_id', $tenant->id)->where('action', 'tenant.document_uploaded')->first();
    expect($uploadLog)->not->toBeNull();

    // Delete
    $deleteResponse = $this->actingAs($tenant)->delete(route('tenant.documents.destroy', $doc));
    $deleteResponse->assertRedirect();

    expect(PropertyDocument::find($doc->id))->toBeNull()
        ->and(Storage::disk('public')->exists($doc->file_path))->toBeFalse();

    $deleteLog = AuditLog::where('user_id', $tenant->id)->where('action', 'tenant.document_deleted')->first();
    expect($deleteLog)->not->toBeNull();
});

test('tenant cannot delete another users tenancy document', function () {
    $tenant1 = User::factory()->create(['role' => 'tenant']);
    $tenant2 = User::factory()->create(['role' => 'tenant']);

    $doc = PropertyDocument::create([
        'user_id' => $tenant1->id,
        'property_id' => null,
        'title' => 'Tenant 1 ID',
        'document_type' => 'national_id',
        'file_path' => 'tenant-documents/id.pdf',
        'file_name' => 'id.pdf',
        'access_level' => 'private',
        'status' => 'verified',
    ]);

    $this->actingAs($tenant2)->delete(route('tenant.documents.destroy', $doc))
        ->assertForbidden();
});

// ── 8. BUTTON & LINK CRAWL VERIFICATION ───────────────────────────────────────

test('tenant dashboard renders with valid navigation anchors, buttons, and form targets', function () {
    $tenant = User::factory()->create(['role' => 'tenant']);

    $response = $this->actingAs($tenant)->get(route('tenant.dashboard'));
    $response->assertOk();

    $content = $response->getContent();

    // Verify all module section IDs exist in the rendered HTML
    $sections = [
        'overview', 'applications', 'leases', 'rent-schedule',
        'maintenance', 'messages', 'documents', 'rentals',
    ];

    foreach ($sections as $section) {
        expect($content)->toContain("id=\"{$section}\"");
    }

    // Verify all action form routes exist
    expect($content)->toContain(route('tenant.applications.store'))
        ->and($content)->toContain(route('tenant.maintenance.store'))
        ->and($content)->toContain(route('tenant.documents.upload'));
});
