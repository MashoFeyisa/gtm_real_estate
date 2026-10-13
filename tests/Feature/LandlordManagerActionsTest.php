<?php

use App\Models\Agent;
use App\Models\Booking;
use App\Models\GuestServiceRequest;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Property;
use App\Models\RentSchedule;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->landlord = User::create([
        'name' => 'Landlord Lelisa',
        'email' => 'landlord.actions@example.com',
        'password' => Hash::make('password123'),
        'role' => 'landlord',
    ]);

    $this->otherLandlord = User::create([
        'name' => 'Other Owner',
        'email' => 'other.owner@example.com',
        'password' => Hash::make('password123'),
        'role' => 'landlord',
    ]);

    $this->manager = User::create([
        'name' => 'Manager Meron',
        'email' => 'manager.actions@example.com',
        'password' => Hash::make('password123'),
        'role' => 'property_manager',
    ]);

    $this->buyer = User::create([
        'name' => 'Buyer Abebe',
        'email' => 'buyer.actions@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->property = Property::create([
        'title' => 'Managed Rental Compound',
        'slug' => 'managed-rental-compound',
        'price' => 5000000,
        'type' => 'rent',
        'status' => 'available',
        'is_active' => true,
    ]);

    $this->foreignProperty = Property::create([
        'title' => 'Foreign Landlord Building',
        'slug' => 'foreign-landlord-building',
        'price' => 6000000,
        'type' => 'rent',
        'status' => 'available',
        'is_active' => true,
    ]);

    $this->lease = Lease::create([
        'lease_code' => 'LSE-ACTIONS-001',
        'property_id' => $this->property->id,
        'landlord_user_id' => $this->landlord->id,
        'tenant_name' => 'Tenant Girma',
        'tenant_email' => 'girma@example.com',
        'tenant_phone' => '+251 91 777 8888',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'monthly_rent' => 45000,
    ]);

    $this->rentSchedule = RentSchedule::create([
        'lease_id' => $this->lease->id,
        'installment_number' => 3,
        'period_label' => 'March 2026',
        'due_date' => '2026-03-05',
        'amount_due' => 45000,
        'amount_paid' => 0,
        'status' => 'pending',
    ]);

    $this->ticket = MaintenanceTicket::create([
        'property_id' => $this->property->id,
        'user_id' => $this->landlord->id,
        'ticket_number' => 'TKT-ACT-0001',
        'title' => 'Leaking kitchen pipe',
        'category' => 'plumbing',
        'priority' => 'high',
        'status' => 'open',
        'description' => 'The kitchen sink pipe leaks every morning.',
    ]);

    $this->foreignTicket = MaintenanceTicket::create([
        'property_id' => $this->foreignProperty->id,
        'user_id' => $this->otherLandlord->id,
        'ticket_number' => 'TKT-ACT-0002',
        'title' => 'Foreign building lift issue',
        'category' => 'structural',
        'priority' => 'low',
        'status' => 'open',
        'description' => 'The lift makes noise on level 3.',
    ]);

    $agent = Agent::create([
        'name' => 'Stay Brokerage',
        'email' => 'stay.brokerage@example.com',
        'phone' => '+251 91 333 2222',
    ]);

    $this->booking = Booking::create([
        'booking_reference' => 'TSTAY-ACT-0001',
        'property_id' => $this->property->id,
        'guest_name' => 'Guest Fikru',
        'guest_email' => 'fikru@example.com',
        'guest_phone' => '+251 91 444 5555',
        'check_in_date' => '2026-11-01',
        'check_out_date' => '2026-11-04',
        'total_nights' => 3,
        'nightly_price' => 5000,
        'accommodation_total' => 15000,
        'total_amount' => 18000,
        'status' => 'confirmed',
    ]);

    $this->serviceRequest = GuestServiceRequest::create([
        'booking_id' => $this->booking->id,
        'property_id' => $this->property->id,
        'service_type' => 'room_cleaning',
        'guest_name' => 'Guest Fikru',
        'description' => 'Room cleaning requested after checkout.',
        'priority' => 'normal',
        'status' => 'pending',
    ]);
});

test('landlord and manager desks render maintenance and rent actions', function () {
    $landlordResponse = $this->actingAs($this->landlord)->get(route('role.portal'));

    $landlordResponse->assertOk();
    $landlordResponse->assertSee('File Maintenance Ticket');
    $landlordResponse->assertSee('Record');
    $landlordResponse->assertSee('Leaking kitchen pipe');

    $managerResponse = $this->actingAs($this->manager)->get(route('role.portal'));

    $managerResponse->assertOk();
    $managerResponse->assertSee('Housekeeping');
    $managerResponse->assertSee('File Maintenance Ticket');
});

test('landlord can advance a maintenance ticket on their own property', function () {
    $this->actingAs($this->landlord)
        ->put(route('maintenance.update-status', $this->ticket), [
            'status' => 'in_progress',
            'resolution_notes' => 'Plumber scheduled for tomorrow.',
        ])
        ->assertRedirect();

    expect($this->ticket->fresh()->status)->toBe('in_progress');

    $this->actingAs($this->landlord)
        ->put(route('maintenance.update-status', $this->ticket->fresh()), ['status' => 'completed'])
        ->assertRedirect();

    $ticket = $this->ticket->fresh();

    expect($ticket->status)->toBe('completed');
    expect($ticket->resolved_at)->not()->toBeNull();
});

test('landlord cannot touch maintenance on a property they do not own', function () {
    $this->actingAs($this->landlord)
        ->put(route('maintenance.update-status', $this->foreignTicket), ['status' => 'completed'])
        ->assertForbidden();

    expect($this->foreignTicket->fresh()->status)->toBe('open');
});

test('landlord can file a maintenance ticket for their own property only', function () {
    $this->actingAs($this->landlord)
        ->post(route('maintenance.store'), [
            'property_id' => $this->property->id,
            'title' => 'Broken window latch',
            'category' => 'structural',
            'priority' => 'medium',
            'description' => 'The living room window latch snapped.',
        ])
        ->assertRedirect();

    expect(MaintenanceTicket::query()->where('title', 'Broken window latch')->exists())->toBeTrue();

    $this->actingAs($this->landlord)
        ->post(route('maintenance.store'), [
            'property_id' => $this->foreignProperty->id,
            'title' => 'Sneaky foreign ticket',
            'category' => 'other',
            'priority' => 'low',
            'description' => 'Should not be allowed.',
        ])
        ->assertForbidden();

    expect(MaintenanceTicket::query()->where('title', 'Sneaky foreign ticket')->exists())->toBeFalse();
});

test('property manager can advance any maintenance ticket and housekeeping request', function () {
    $this->actingAs($this->manager)
        ->put(route('maintenance.update-status', $this->ticket), ['status' => 'assigned', 'assigned_to' => $this->manager->id])
        ->assertRedirect();

    expect($this->ticket->fresh()->assigned_to)->toBe($this->manager->id);

    $this->actingAs($this->manager)
        ->put(route('housekeeping-requests.update-status', $this->serviceRequest), ['status' => 'completed'])
        ->assertRedirect();

    expect($this->serviceRequest->fresh()->status)->toBe('completed');
    expect($this->serviceRequest->fresh()->handled_by)->toBe($this->manager->id);
});

test('landlord can record a rent payment which settles the installment', function () {
    $this->actingAs($this->landlord)
        ->post(route('rent-schedules.record-payment', $this->rentSchedule), [
            'amount' => 45000,
            'payment_method' => 'telebirr',
            'bank_transaction_reference' => 'TB-123456',
        ])
        ->assertRedirect();

    $schedule = $this->rentSchedule->fresh();

    expect($schedule->status)->toBe('paid');
    expect((float) $schedule->amount_paid)->toBe(45000.0);

    $payment = Payment::query()->where('payable_id', $schedule->id)->first();

    expect($payment)->not()->toBeNull();
    expect($payment->payment_type)->toBe('rent_installment');
    expect($payment->payer_name)->toBe('Tenant Girma');
    expect($payment->status)->toBe('completed');
});

test('partial rent payment leaves the installment partial', function () {
    $this->actingAs($this->landlord)
        ->post(route('rent-schedules.record-payment', $this->rentSchedule), [
            'amount' => 15000,
            'payment_method' => 'cash',
        ])
        ->assertRedirect();

    $schedule = $this->rentSchedule->fresh();

    expect($schedule->status)->toBe('partial');
    expect((float) $schedule->amount_paid)->toBe(15000.0);
});

test('rent payment and maintenance actions are permission gated', function () {
    UserPermissionOverride::create([
        'user_id' => $this->landlord->id,
        'permission_id' => Permission::query()->where('key', 'maintenance.manage')->value('id'),
        'granted' => false,
    ]);

    UserPermissionOverride::create([
        'user_id' => $this->landlord->id,
        'permission_id' => Permission::query()->where('key', 'rent.manage')->value('id'),
        'granted' => false,
    ]);

    $this->actingAs($this->landlord->fresh())
        ->put(route('maintenance.update-status', $this->ticket), ['status' => 'completed'])
        ->assertForbidden();

    $this->actingAs($this->landlord->fresh())
        ->post(route('rent-schedules.record-payment', $this->rentSchedule), [
            'amount' => 45000,
            'payment_method' => 'cash',
        ])
        ->assertForbidden();

    expect($this->ticket->fresh()->status)->toBe('open');
    expect($this->rentSchedule->fresh()->status)->toBe('pending');
});

test('buyers cannot use the maintenance or housekeeping actions', function () {
    $this->actingAs($this->buyer)
        ->put(route('maintenance.update-status', $this->ticket), ['status' => 'completed'])
        ->assertForbidden();

    $this->actingAs($this->buyer)
        ->put(route('housekeeping-requests.update-status', $this->serviceRequest), ['status' => 'completed'])
        ->assertForbidden();

    expect($this->ticket->fresh()->status)->toBe('open');
    expect($this->serviceRequest->fresh()->status)->toBe('pending');
});

test('admins keep full maintenance access', function () {
    $admin = User::create([
        'name' => 'Maintenance Admin',
        'email' => 'admin.maintenance@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->put(route('maintenance.update-status', $this->ticket), ['status' => 'closed'])
        ->assertRedirect();

    expect($this->ticket->fresh()->status)->toBe('closed');
});
