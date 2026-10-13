<?php

use App\Models\Agent;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\PropertyViewing;
use App\Models\RentSchedule;
use App\Models\User;
use App\Notifications\RentDueReminderNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::create([
        'name' => 'Admin Alula',
        'email' => 'admin.alula@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->agentUser = User::create([
        'name' => 'Agent Selam',
        'email' => 'agent.selam@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Selam Properties',
        'email' => 'selam@example.com',
        'phone' => '+251 91 222 3344',
        'is_active' => true,
    ]);

    $this->tenantUser = User::create([
        'name' => 'Tenant Solomon',
        'email' => 'solomon@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->property = Property::create([
        'property_code' => 'ETH-AA-RES-00000001',
        'title' => 'Bole Medhanealem Luxury Flat',
        'slug' => 'bole-medhanealem-luxury-flat',
        'price' => 50000.00,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 120,
        'building_area' => 120,
        'type' => 'rent',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole, Addis Ababa',
        'agent_id' => $this->agent->id,
    ]);
});

test('process rent schedules command escalates overdue installment past grace period and applies late fee', function () {
    $lease = Lease::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'landlord_user_id' => $this->adminUser->id,
        'tenant_user_id' => $this->tenantUser->id,
        'tenant_name' => $this->tenantUser->name,
        'tenant_email' => $this->tenantUser->email,
        'tenant_phone' => '+251911001122',
        'start_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(10)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 50000.00,
        'security_deposit' => 100000.00,
        'payment_frequency' => 'monthly',
        'grace_period_days' => 5,
        'late_fee_percentage' => 5.0, // 5% late fee surcharge
        'status' => 'active',
    ]);

    // Create installment due 15 days ago (past 5-day grace period)
    $overdueSchedule = RentSchedule::create([
        'lease_id' => $lease->id,
        'installment_number' => 1,
        'period_label' => 'መስከረም 2018 (Meskerem 2018)',
        'due_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
        'amount_due' => 50000.00,
        'amount_paid' => 0.00,
        'status' => 'pending',
    ]);

    Artisan::call('rents:process-schedules', ['--notify' => true]);

    $overdueSchedule->refresh();
    expect($overdueSchedule->status)->toBe('overdue');
    // Amount due should include 5% late fee = 50,000 + 2,500 = 52,500 ETB
    expect((float) $overdueSchedule->amount_due)->toBe(52500.0);
    expect($overdueSchedule->notes)->toContain('Late fee applied');

    // Tenant should have received database notification
    $notification = $this->tenantUser->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['type'])->toBe('rent_schedule');
    expect($notification->data['is_overdue'])->toBeTrue();
    expect((float) $notification->data['late_fee_amount'])->toBe(2500.0);
});

test('process rent schedules command dispatches upcoming reminder within 3 days', function () {
    $lease = Lease::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'landlord_user_id' => $this->adminUser->id,
        'tenant_user_id' => $this->tenantUser->id,
        'tenant_name' => $this->tenantUser->name,
        'tenant_email' => $this->tenantUser->email,
        'tenant_phone' => '+251911001122',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 50000.00,
        'status' => 'active',
    ]);

    // Due in 2 days
    $upcomingSchedule = RentSchedule::create([
        'lease_id' => $lease->id,
        'installment_number' => 1,
        'period_label' => 'ጥቅምት 2018 (Tikimt 2018)',
        'due_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
        'amount_due' => 50000.00,
        'amount_paid' => 0.00,
        'status' => 'pending',
    ]);

    Artisan::call('rents:process-schedules', ['--notify' => true]);

    $notification = $this->tenantUser->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['is_overdue'])->toBeFalse();
    expect($notification->data['title'])->toContain('Upcoming Rent Due');
});

test('admin can trigger audit and escalation check via web endpoint', function () {
    $response = $this->actingAs($this->adminUser)->post(route('leases.process-schedules'));

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

test('unauthorized client cannot trigger rent audit endpoint', function () {
    $response = $this->actingAs($this->tenantUser)->post(route('leases.process-schedules'));

    $response->assertStatus(403);
});

test('user can mark in-app notification as read', function () {
    $this->tenantUser->notify(new RentDueReminderNotification(
        new RentSchedule(['id' => 99, 'period_label' => 'Meskerem', 'amount_due' => 45000]),
        isOverdue: false
    ));

    $notification = $this->tenantUser->unreadNotifications()->first();
    expect($notification)->not->toBeNull();

    $response = $this->actingAs($this->tenantUser)
        ->post(route('notifications.read', $notification->id));

    $response->assertRedirect();
    $this->tenantUser->refresh();
    expect($this->tenantUser->unreadNotifications()->count())->toBe(0);
});

test('user can mark all notifications as read', function () {
    $this->tenantUser->notify(new RentDueReminderNotification(
        new RentSchedule(['id' => 1, 'period_label' => 'Meskerem', 'amount_due' => 45000]),
        isOverdue: false
    ));
    $this->tenantUser->notify(new RentDueReminderNotification(
        new RentSchedule(['id' => 2, 'period_label' => 'Tikimt', 'amount_due' => 45000]),
        isOverdue: false
    ));

    expect($this->tenantUser->unreadNotifications()->count())->toBe(2);

    $response = $this->actingAs($this->tenantUser)
        ->post(route('notifications.mark-all-read'));

    $response->assertRedirect();
    $this->tenantUser->refresh();
    expect($this->tenantUser->unreadNotifications()->count())->toBe(0);
});

test('viewing status confirmation dispatches in-app notification to buyer', function () {
    $viewing = PropertyViewing::create([
        'property_id' => $this->property->id,
        'user_id' => $this->tenantUser->id,
        'buyer_name' => $this->tenantUser->name,
        'buyer_email' => $this->tenantUser->email,
        'buyer_phone' => '+251911000000',
        'viewing_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
        'viewing_time' => '14:00:00',
        'viewing_type' => 'in_person',
        'status' => 'requested',
    ]);

    $this->actingAs($this->adminUser)->put(route('viewings.update-status', $viewing->id), [
        'status' => 'confirmed',
    ]);

    $notification = $this->tenantUser->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['type'])->toBe('property_viewing');
    expect($notification->data['status'])->toBe('confirmed');
    expect($notification->data['title'])->toContain('Confirmed');
});

test('counteroffer and offer acceptance dispatch notifications to buyer', function () {
    $offer = PropertyOffer::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'user_id' => $this->tenantUser->id,
        'buyer_name' => $this->tenantUser->name,
        'buyer_email' => $this->tenantUser->email,
        'buyer_phone' => '+251911000000',
        'offer_type' => 'rental',
        'original_price' => 50000,
        'offered_price' => 45000,
        'currency' => 'ETB',
        'financing_type' => 'cash',
        'status' => 'submitted',
    ]);

    // Agent submits counteroffer
    $this->actingAs($this->agentUser)->post(route('offers.counter', $offer->id), [
        'counter_amount' => 48000,
        'message' => 'Can meet at 48k ETB.',
    ]);

    $counterNotif = $this->tenantUser->notifications()->first();
    expect($counterNotif)->not->toBeNull();
    expect($counterNotif->data['action'])->toBe('countered');
    expect($counterNotif->data['title'])->toContain('Counteroffer Received');

    // Agent accepts offer
    $this->actingAs($this->agentUser)->put(route('offers.accept', $offer->id));

    $this->tenantUser->refresh();
    expect($this->tenantUser->notifications()->count())->toBe(2);

    $acceptNotif = $this->tenantUser->notifications()->get()->firstWhere('data.action', 'accepted');
    expect($acceptNotif)->not->toBeNull();
    expect($acceptNotif->data['action'])->toBe('accepted');
    expect($acceptNotif->data['title'])->toContain('Accepted');
});

test('payment verification dispatches receipt notification to payer', function () {
    $payment = Payment::create([
        'property_id' => $this->property->id,
        'user_id' => $this->tenantUser->id,
        'payer_name' => $this->tenantUser->name,
        'payer_email' => $this->tenantUser->email,
        'payer_phone' => '+251911000000',
        'payment_type' => 'rent_installment',
        'payment_method' => 'bank_transfer',
        'currency' => 'ETB',
        'amount' => 50000.00,
        'platform_fee' => 0.00,
        'tax_amount' => 0.00,
        'net_amount' => 50000.00,
        'status' => 'pending',
    ]);

    $this->actingAs($this->adminUser)->put(route('payments.verify', $payment->id), [
        'bank_transaction_reference' => 'CBE99887766',
    ]);

    $notification = $this->tenantUser->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['type'])->toBe('payment');
    expect($notification->data['status'])->toBe('completed');
    expect($notification->data['title'])->toContain('Verified');
});
