<?php

use App\Models\AuditLog;
use App\Models\CleaningTask;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\PropertyInspection;
use App\Models\RentSchedule;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');
});

// ── 1. ACCESS & BOUNDARY CONTROLS ─────────────────────────────────────────────

test('property manager can view control center dashboard with unit management modal', function () {
    $pm = User::factory()->create([
        'name' => 'Dawit Manager',
        'role' => 'property_manager',
    ]);

    $property = Property::factory()->create([
        'user_id' => $pm->id,
        'title' => 'Sample Managed Residence',
        'city' => 'Addis Ababa',
        'price' => 50000,
    ]);

    $response = $this->actingAs($pm)->get(route('property-manager.dashboard'));

    $response->assertOk();
    $response->assertSee('Property Operations');
    $response->assertSee('Units &amp; Occupancy', false);
    $response->assertSee('Add New Unit');
    $response->assertSee('add-unit-modal');
    $response->assertSee('Sample Managed Residence');
    $response->assertSee('Manage / Edit');
});

test('admin cannot open individual property manager dashboard due to boundary enforcement', function () {
    $admin = User::factory()->create([
        'name' => 'Platform Admin',
        'email' => 'admin@example.com',
        'role' => 'admin',
    ]);

    $this->actingAs($admin)->get(route('property-manager.dashboard'))
        ->assertForbidden();
});

test('suspended property manager cannot access dashboard', function () {
    $pm = User::factory()->create([
        'role' => 'property_manager',
        'status' => 'suspended',
    ]);

    $this->actingAs($pm)->get(route('property-manager.dashboard'))
        ->assertForbidden();
});

test('regular client cannot access property manager endpoints', function () {
    $client = User::factory()->create(['role' => 'buyer']);
    $property = Property::factory()->create();

    $this->actingAs($client)->get(route('property-manager.dashboard'))
        ->assertForbidden();

    $this->actingAs($client)->post(route('property_manager.units.store'), [
        'title' => 'Unauthorized Unit',
        'price' => 20000,
    ])->assertForbidden();

    $this->actingAs($client)->put(route('property_manager.units.update', $property->id), [
        'title' => 'Unauthorized Unit Update',
        'price' => 25000,
        'status' => 'available',
    ])->assertForbidden();
});

// ── 2. MULTI-UNIT HIERARCHY & PORTFOLIO CRUD ──────────────────────────────────

test('property manager can register parent building and attach child units', function () {
    $pm = User::factory()->create(['role' => 'property_manager']);

    // 1. Register parent building
    $buildingResponse = $this->actingAs($pm)->post(route('property_manager.buildings.store'), [
        'title' => 'Meskel Flower Executive Heights',
        'property_category' => 'apartment',
        'city' => 'Addis Ababa',
        'subcity' => 'Kirkos',
        'address' => 'Near Meskel Flower Hotel',
        'floors' => 8,
        'description' => 'Modern 8-story multi-unit residential tower with 24 units.',
    ]);
    $buildingResponse->assertRedirect();

    $building = Property::where('user_id', $pm->id)->where('title', 'Meskel Flower Executive Heights')->first();
    expect($building)->not->toBeNull()
        ->and($building->parent_id)->toBeNull()
        ->and($building->floors)->toBe(8);

    // 2. Register child unit under parent building
    $unitResponse = $this->actingAs($pm)->post(route('property_manager.units.store'), [
        'parent_id' => $building->id,
        'unit_number' => 'Unit 402',
        'title' => 'Meskel Flower Executive Heights - Unit 402',
        'property_category' => 'apartment',
        'type' => 'rent',
        'price' => 45000,
        'city' => 'Addis Ababa',
        'subcity' => 'Kirkos',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 95,
        'status' => 'available',
        'image' => UploadedFile::fake()->image('unit402.jpg'),
    ]);
    $unitResponse->assertRedirect();

    $unit = Property::where('user_id', $pm->id)->where('unit_number', 'Unit 402')->first();
    expect($unit)->not->toBeNull()
        ->and($unit->parent_id)->toBe($building->id)
        ->and((float) $unit->price)->toBe(45000.0)
        ->and(Storage::disk('public')->exists($unit->image_path))->toBeTrue();
});

test('property manager can update unit and transition occupancy status', function () {
    $pm = User::factory()->create(['role' => 'property_manager']);
    $unit = Property::factory()->create([
        'user_id' => $pm->id,
        'title' => 'Original Managed Unit',
        'price' => 30000,
        'status' => 'available',
    ]);

    // 1. Update unit details
    $updateResponse = $this->actingAs($pm)->put(route('property_manager.units.update', $unit), [
        'title' => 'Renovated Bole Unit 101',
        'unit_number' => '101',
        'price' => 38000,
        'status' => 'rented',
        'property_category' => 'condominium',
        'type' => 'rent',
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 80,
    ]);
    $updateResponse->assertRedirect();

    $fresh = $unit->fresh();
    expect($fresh->title)->toBe('Renovated Bole Unit 101')
        ->and((float) $fresh->price)->toBe(38000.0)
        ->and($fresh->status)->toBe('rented');

    // 2. Quick status transition to turnover
    $statusResponse = $this->actingAs($pm)->patch(route('property_manager.units.status', [$unit, 'turnover']));
    $statusResponse->assertRedirect();
    expect($unit->fresh()->status)->toBe('turnover');

    // 3. Delete unit
    $deleteResponse = $this->actingAs($pm)->delete(route('property_manager.units.destroy', $unit));
    $deleteResponse->assertRedirect();
    expect(Property::find($unit->id))->toBeNull();
});

test('property manager cannot update or delete another managers unit', function () {
    $pm1 = User::factory()->create(['role' => 'property_manager']);
    $pm2 = User::factory()->create(['role' => 'property_manager']);
    $unit = Property::factory()->create(['user_id' => $pm1->id]);

    $this->actingAs($pm2)->put(route('property_manager.units.update', $unit), [
        'title' => 'Hacked Unit',
        'price' => 100,
        'status' => 'available',
    ])->assertForbidden();

    $this->actingAs($pm2)->delete(route('property_manager.units.destroy', $unit))
        ->assertForbidden();
});

// ── 3. TENANT LEDGER & DELINQUENCY ESCALATION ─────────────────────────────────

test('property manager can adjust rent schedule balance and issue delinquency notices', function () {
    $pm = User::factory()->create(['role' => 'property_manager']);
    $tenant = User::factory()->create(['role' => 'tenant']);
    $unit = Property::factory()->create(['user_id' => $pm->id]);

    $lease = Lease::create([
        'property_id' => $unit->id,
        'landlord_user_id' => $pm->id,
        'tenant_user_id' => $tenant->id,
        'tenant_name' => 'Senait Haile',
        'tenant_email' => 'senait@example.com',
        'tenant_phone' => '+251911445566',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(12)->toDateString(),
        'duration_months' => 12,
        'monthly_rent' => 25000,
        'security_deposit' => 50000,
        'status' => 'active',
    ]);

    $schedule = RentSchedule::create([
        'lease_id' => $lease->id,
        'installment_number' => 1,
        'period_label' => 'March 2026',
        'due_date' => now()->subDays(5)->toDateString(),
        'amount_due' => 25000,
        'amount_paid' => 0,
        'status' => 'pending',
    ]);

    // 1. Adjust balance (5000 ETB loyalty discount credit)
    $adjustResponse = $this->actingAs($pm)->put(route('property_manager.rent.adjust', $schedule), [
        'new_amount_due' => 20000,
        'adjustment_reason' => 'Granted 5,000 ETB concession for plumbing downtime.',
    ]);
    $adjustResponse->assertRedirect();

    $freshSchedule = $schedule->fresh();
    expect((float) $freshSchedule->amount_due)->toBe(20000.0)
        ->and($freshSchedule->notes)->toContain('5,000 ETB concession');

    $adjustLog = AuditLog::where('user_id', $pm->id)->where('action', 'property_manager.rent_adjusted')->first();
    expect($adjustLog)->not->toBeNull();

    // 2. Escalate delinquency
    $escalateResponse = $this->actingAs($pm)->post(route('property_manager.rent.escalate', $schedule), [
        'notice_type' => 'first_notice',
        'notice_notes' => 'Installment past due by 5 days. Kindly settle immediately.',
    ]);
    $escalateResponse->assertRedirect();

    expect($schedule->fresh()->status)->toBe('overdue')
        ->and($schedule->fresh()->notes)->toContain('First notice');
});

// ── 4. MOVE-IN & MOVE-OUT CONDITION INSPECTIONS ───────────────────────────────

test('property manager can conduct move-in and move-out inspections', function () {
    $pm = User::factory()->create(['role' => 'property_manager']);
    $tenant = User::factory()->create(['role' => 'tenant']);
    $unit = Property::factory()->create(['user_id' => $pm->id]);

    $lease = Lease::create([
        'property_id' => $unit->id,
        'landlord_user_id' => $pm->id,
        'tenant_user_id' => $tenant->id,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'tenant_phone' => '+251900000000',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(12)->toDateString(),
        'duration_months' => 12,
        'monthly_rent' => 30000,
        'security_deposit' => 60000,
        'status' => 'active',
    ]);

    $response = $this->actingAs($pm)->post(route('property_manager.inspections.store', $unit), [
        'inspection_type' => 'move_in',
        'overall_condition' => 'excellent',
        'inspection_date' => now()->toDateString(),
        'lease_id' => $lease->id,
        'notes' => 'All walls pristine white, keys tested, AC working, electrical outlets inspected.',
        'checklist' => [
            'keys_and_locks' => 'pass',
            'walls_and_paint' => 'pass',
            'plumbing_and_taps' => 'pass',
            'electrical_and_lighting' => 'pass',
            'kitchen_appliances' => 'pass',
        ],
    ]);
    $response->assertRedirect();

    $inspection = PropertyInspection::where('property_id', $unit->id)->first();
    expect($inspection)->not->toBeNull()
        ->and($inspection->inspection_type)->toBe('move_in')
        ->and($inspection->overall_condition)->toBe('excellent')
        ->and($inspection->inspector_user_id)->toBe($pm->id)
        ->and($inspection->checklist['walls_and_paint'])->toBe('pass');

    $log = AuditLog::where('user_id', $pm->id)->where('action', 'property_manager.inspection_completed')->first();
    expect($log)->not->toBeNull();
});

// ── 5. MAINTENANCE CONTRACTOR DISPATCH & CLEANING ────────────────────────────

test('property manager can assign contractor with quotes, close ticket, and dispatch cleaning', function () {
    $pm = User::factory()->create(['role' => 'property_manager']);
    $cleaner = User::factory()->create(['role' => 'cleaner']);
    $unit = Property::factory()->create(['user_id' => $pm->id]);

    $ticket = MaintenanceTicket::create([
        'user_id' => $pm->id,
        'property_id' => $unit->id,
        'ticket_number' => 'TCK-PM-01',
        'title' => 'Generator Automatic Transfer Switch Failure',
        'category' => 'electrical',
        'priority' => 'high',
        'description' => 'Generator runs but fails to transfer power to apartment units.',
        'status' => 'open',
    ]);

    // 1. Assign contractor with quote
    $assignResponse = $this->actingAs($pm)->put(route('property_manager.maintenance.assign', $ticket), [
        'assigned_contractor' => 'Ethio Power & Automation Systems',
        'estimated_cost' => 14500,
        'location_notes' => 'Technicians scheduled on-site tomorrow at 9am.',
    ]);
    $assignResponse->assertRedirect();

    expect($ticket->fresh()->status)->toBe('in_progress')
        ->and((float) $ticket->fresh()->estimated_cost)->toBe(14500.0)
        ->and($ticket->fresh()->location_notes)->toContain('Ethio Power');

    // 2. Complete and close maintenance
    $closeResponse = $this->actingAs($pm)->put(route('property_manager.maintenance.close', $ticket), [
        'actual_cost' => 14000,
        'resolution_notes' => 'Contactor coil replaced and ATS calibrated. Dual power tested successfully.',
    ]);
    $closeResponse->assertRedirect();

    expect($ticket->fresh()->status)->toBe('closed')
        ->and((float) $ticket->fresh()->actual_cost)->toBe(14000.0)
        ->and($ticket->fresh()->resolved_at)->not->toBeNull();

    // 3. Dispatch cleaning task
    $dispatchResponse = $this->actingAs($pm)->post(route('property_manager.cleaning.dispatch'), [
        'property_id' => $unit->id,
        'cleaner_user_id' => $cleaner->id,
        'room_number' => 'Unit 204',
        'priority' => 'urgent',
        'scheduled_date' => now()->toDateString(),
        'notes' => 'Turnover cleaning following tenant move-out.',
    ]);
    $dispatchResponse->assertRedirect();

    $task = CleaningTask::where('property_id', $unit->id)->first();
    expect($task)->not->toBeNull()
        ->and($task->status)->toBe('assigned');

    // 4. Mark cleaning inspected and ready
    $completeCleaning = $this->actingAs($pm)->put(route('property_manager.cleaning.complete', $task));
    $completeCleaning->assertRedirect();

    expect($task->fresh()->status)->toBe('ready')
        ->and($task->fresh()->inspected_by)->toBe($pm->id)
        ->and($task->fresh()->ready_at)->not->toBeNull();
});

test('property manager can download tenancy agreement document', function () {
    Storage::fake('local');

    $pm = User::factory()->create(['role' => 'property_manager']);
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create(['user_id' => $pm->id]);

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
