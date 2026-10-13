<?php

use App\Models\Agent;
use App\Models\Booking;
use App\Models\GuestServiceRequest;
use App\Models\Inquiry;
use App\Models\Permission;
use App\Models\Property;
use App\Models\PropertyReport;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->support = User::create([
        'name' => 'Support Sara',
        'email' => 'support.actions@example.com',
        'password' => Hash::make('password123'),
        'role' => 'customer_support',
    ]);

    $this->provider = User::create([
        'name' => 'Provider Petros',
        'email' => 'provider.actions@example.com',
        'password' => Hash::make('password123'),
        'role' => 'service_provider',
    ]);

    $this->owner = User::create([
        'name' => 'Owner Hanna',
        'email' => 'owner.actions@example.com',
        'password' => Hash::make('password123'),
        'role' => 'property_owner',
    ]);

    $this->buyer = User::create([
        'name' => 'Buyer Dawit',
        'email' => 'buyer.pass5@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $agent = Agent::create([
        'name' => 'Pass 5 Brokerage',
        'email' => 'pass5.brokerage@example.com',
        'phone' => '+251 91 666 7777',
    ]);

    $property = Property::create([
        'title' => 'Pass Five Property',
        'slug' => 'pass-five-property',
        'price' => 3200000,
        'type' => 'rent',
        'status' => 'available',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $this->booking = Booking::create([
        'booking_reference' => 'TSTAY-P5-0001',
        'property_id' => $property->id,
        'user_id' => $this->owner->id,
        'guest_name' => 'Owner Hanna',
        'guest_email' => 'owner.actions@example.com',
        'guest_phone' => '+251 91 888 9999',
        'check_in_date' => '2026-12-01',
        'check_out_date' => '2026-12-05',
        'total_nights' => 4,
        'nightly_price' => 4000,
        'accommodation_total' => 16000,
        'total_amount' => 19000,
        'status' => 'confirmed',
    ]);

    $this->serviceRequest = GuestServiceRequest::create([
        'booking_id' => $this->booking->id,
        'property_id' => $property->id,
        'service_type' => 'maintenance',
        'guest_name' => 'Owner Hanna',
        'description' => 'Air conditioning not cooling.',
        'priority' => 'urgent',
        'status' => 'pending',
    ]);

    $this->inquiry = Inquiry::create([
        'name' => 'Buyer Dawit',
        'email' => 'buyer.pass5@example.com',
        'phone' => '+251 91 000 1122',
        'subject' => 'Question about rent terms',
        'message' => 'Is the rent negotiable for a 12 month lease?',
        'status' => 'new',
        'agent_id' => $agent->id,
    ]);

    $this->report = PropertyReport::create([
        'report_number' => 'RPT-P5-0001',
        'subject' => 'Refund dispute',
        'reason' => 'payment',
        'description' => 'Guest disputes the withheld deposit on checkout.',
        'status' => 'pending',
    ]);
});

test('support provider and owner desks render their action forms', function () {
    $supportResponse = $this->actingAs($this->support)->get(route('role.portal'));
    $supportResponse->assertOk();
    $supportResponse->assertSee('User Tickets');
    $supportResponse->assertSee('Disputes');

    $providerResponse = $this->actingAs($this->provider)->get(route('role.portal'));
    $providerResponse->assertOk();
    $providerResponse->assertSee('Job Invitations');

    $ownerResponse = $this->actingAs($this->owner)->get(route('role.portal'));
    $ownerResponse->assertOk();
    $ownerResponse->assertSee('Bookings');
});

test('customer support can triage an inquiry', function () {
    $this->actingAs($this->support)
        ->put(route('inquiries.update-status', $this->inquiry), ['status' => 'contacted'])
        ->assertRedirect();

    $inquiry = $this->inquiry->fresh();

    expect($inquiry->status)->toBe('contacted');
    expect($inquiry->read_at)->not()->toBeNull();
});

test('customer support can resolve a dispute', function () {
    $this->actingAs($this->support)
        ->put(route('reports.update', $this->report), [
            'status' => 'resolved',
            'resolution_notes' => 'Deposit released to the guest after review.',
        ])
        ->assertRedirect();

    $report = $this->report->fresh();

    expect($report->status)->toBe('resolved');
    expect($report->resolved_by)->toBe($this->support->id);
});

test('service provider can respond to a job invitation', function () {
    $this->actingAs($this->provider)
        ->put(route('housekeeping-requests.update-status', $this->serviceRequest), [
            'status' => 'in_progress',
            'response_notes' => 'Technician on the way.',
        ])
        ->assertRedirect();

    $request = $this->serviceRequest->fresh();

    expect($request->status)->toBe('in_progress');
    expect($request->handled_by)->toBe($this->provider->id);
});

test('property owner can update the status of their own booking', function () {
    $this->actingAs($this->owner)
        ->put(route('bookings.update-status', $this->booking), ['status' => 'completed'])
        ->assertRedirect();

    expect($this->booking->fresh()->status)->toBe('completed');
});

test('pass five actions are permission and role gated', function () {
    $this->actingAs($this->buyer)
        ->put(route('inquiries.update-status', $this->inquiry), ['status' => 'closed'])
        ->assertForbidden();

    $this->actingAs($this->buyer)
        ->put(route('housekeeping-requests.update-status', $this->serviceRequest), ['status' => 'completed'])
        ->assertForbidden();

    $this->actingAs($this->buyer)
        ->put(route('reports.update', $this->report), ['status' => 'resolved'])
        ->assertForbidden();

    $this->actingAs($this->owner)
        ->put(route('reports.update', $this->report), ['status' => 'resolved'])
        ->assertForbidden();

    expect($this->inquiry->fresh()->status)->toBe('new');
    expect($this->report->fresh()->status)->toBe('pending');
});

test('revoking permissions blocks the desk actions', function () {
    UserPermissionOverride::create([
        'user_id' => $this->support->id,
        'permission_id' => Permission::query()->where('key', 'tickets.manage')->value('id'),
        'granted' => false,
    ]);

    UserPermissionOverride::create([
        'user_id' => $this->provider->id,
        'permission_id' => Permission::query()->where('key', 'services.jobs_receive')->value('id'),
        'granted' => false,
    ]);

    UserPermissionOverride::create([
        'user_id' => $this->owner->id,
        'permission_id' => Permission::query()->where('key', 'bookings.update')->value('id'),
        'granted' => false,
    ]);

    $this->actingAs($this->support->fresh())
        ->put(route('inquiries.update-status', $this->inquiry), ['status' => 'closed'])
        ->assertForbidden();

    $this->actingAs($this->provider->fresh())
        ->put(route('housekeeping-requests.update-status', $this->serviceRequest), ['status' => 'completed'])
        ->assertForbidden();

    $this->actingAs($this->owner->fresh())
        ->put(route('bookings.update-status', $this->booking), ['status' => 'completed'])
        ->assertForbidden();

    expect($this->inquiry->fresh()->status)->toBe('new');
    expect($this->serviceRequest->fresh()->status)->toBe('pending');
    expect($this->booking->fresh()->status)->toBe('confirmed');
});

test('admins keep access to the pass five actions', function () {
    $admin = User::create([
        'name' => 'Pass Five Admin',
        'email' => 'admin.pass5@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->put(route('inquiries.update-status', $this->inquiry), ['status' => 'closed'])
        ->assertRedirect();

    $this->actingAs($admin)
        ->put(route('housekeeping-requests.update-status', $this->serviceRequest), ['status' => 'completed'])
        ->assertRedirect();

    expect($this->inquiry->fresh()->status)->toBe('closed');
    expect($this->serviceRequest->fresh()->status)->toBe('completed');
});
