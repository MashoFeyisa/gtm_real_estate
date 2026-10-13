<?php

use App\Models\Agent;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\RentSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

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

    $this->rentalProperty = Property::create([
        'property_code' => 'ETH-AA-KIR-00000001',
        'title' => 'Kazanchis Luxury Apartment 3BR',
        'slug' => 'kazanchis-luxury-apartment-3br',
        'price' => 45000.00,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 160,
        'building_area' => 160,
        'type' => 'rent',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Kazanchis, Addis Ababa',
        'agent_id' => $this->agent->id,
    ]);
});

test('admin can create a lease for a rental property and rent schedules are automatically generated', function () {
    $response = $this->actingAs($this->adminUser)
        ->post(route('leases.store'), [
            'property_id' => $this->rentalProperty->id,
            'tenant_name' => 'Solomon Kassa',
            'tenant_email' => 'solomon@example.com',
            'tenant_phone' => '0911223344',
            'tenant_id_type' => 'kebele_id',
            'tenant_id_number' => 'KB-AA-12345',
            'tenant_occupation' => 'Software Engineer',
            'emergency_contact_name' => 'Kassa Bekele',
            'emergency_contact_phone' => '0911889900',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'duration_months' => 12,
            'monthly_rent' => 45000.00,
            'security_deposit' => 90000.00,
            'payment_frequency' => 'monthly',
            'grace_period_days' => 5,
            'late_fee_percentage' => 2.5,
            'terms_and_conditions' => 'Standard residential tenancy terms.',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('leases', [
        'property_id' => $this->rentalProperty->id,
        'tenant_name' => 'Solomon Kassa',
        'tenant_email' => 'solomon@example.com',
        'tenant_user_id' => $this->tenantUser->id,
        'monthly_rent' => 45000.00,
        'duration_months' => 12,
        'status' => 'active',
    ]);

    $lease = Lease::where('property_id', $this->rentalProperty->id)->first();
    expect($lease)->not->toBeNull()
        ->and($lease->lease_code)->toStartWith('LSE-ETH-')
        ->and($lease->schedules)->toHaveCount(12);

    $firstSchedule = $lease->schedules->first();
    expect((float) $firstSchedule->amount_due)->toBe(45000.00)
        ->and($firstSchedule->status)->toBe('pending');
});

test('agent can create a lease for their assigned rental property', function () {
    $response = $this->actingAs($this->agentUser)
        ->post(route('leases.store'), [
            'property_id' => $this->rentalProperty->id,
            'tenant_name' => 'Sara Tadesse',
            'tenant_email' => 'sara@example.com',
            'tenant_phone' => '0912345678',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'duration_months' => 6,
            'monthly_rent' => 40000.00,
            'security_deposit' => 40000.00,
            'payment_frequency' => 'quarterly',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $lease = Lease::where('tenant_name', 'Sara Tadesse')->first();
    expect($lease)->not->toBeNull()
        ->and($lease->duration_months)->toBe(6)
        ->and($lease->payment_frequency)->toBe('quarterly');

    // 6 months with quarterly frequency = 2 installments, each 40000 * 3 = 120,000 ETB
    expect($lease->schedules)->toHaveCount(2);
    expect((float) $lease->schedules->first()->amount_due)->toBe(120000.00);
});

test('unauthorized user cannot create a lease for an unassigned property', function () {
    $otherAgentUser = User::create([
        'name' => 'Other Agent',
        'email' => 'other.agent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    Agent::create([
        'user_id' => $otherAgentUser->id,
        'name' => 'Other Agent Firm',
        'email' => 'other@example.com',
        'phone' => '+251 91 000 0000',
        'is_active' => true,
    ]);

    $response = $this->actingAs($otherAgentUser)
        ->post(route('leases.store'), [
            'property_id' => $this->rentalProperty->id,
            'tenant_name' => 'Intruder Tenant',
            'tenant_email' => 'intruder@example.com',
            'tenant_phone' => '0900000000',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'duration_months' => 12,
            'monthly_rent' => 45000.00,
            'payment_frequency' => 'monthly',
        ]);

    $response->assertForbidden();
});

test('can fetch lease details and full schedule via json endpoint', function () {
    $lease = Lease::create([
        'property_id' => $this->rentalProperty->id,
        'agent_id' => $this->agent->id,
        'tenant_name' => 'Martha Girma',
        'tenant_email' => 'martha@example.com',
        'tenant_phone' => '0922334455',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 35000.00,
        'security_deposit' => 35000.00,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);
    $lease->generateSchedules();

    $response = $this->actingAs($this->adminUser)
        ->getJson(route('leases.show', $lease->id));

    $response->assertOk()
        ->assertJsonStructure([
            'lease' => ['id', 'lease_code', 'tenant_name', 'monthly_rent'],
            'schedules',
            'summary' => ['total_rent_due', 'total_rent_paid', 'outstanding_balance'],
        ]);

    expect($response->json('lease.tenant_name'))->toBe('Martha Girma')
        ->and($response->json('schedules'))->toHaveCount(12);
});

test('admin can update lease lifecycle status', function () {
    $lease = Lease::create([
        'property_id' => $this->rentalProperty->id,
        'agent_id' => $this->agent->id,
        'tenant_name' => 'Dawit Yilma',
        'tenant_email' => 'dawit@example.com',
        'tenant_phone' => '0933445566',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 30000.00,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->adminUser)
        ->put(route('leases.update-status', $lease->id), [
            'status' => 'terminated',
            'notes' => 'Early termination agreed between parties.',
        ]);

    $response->assertRedirect();
    expect($lease->fresh()->status)->toBe('terminated')
        ->and($lease->fresh()->notes)->toBe('Early termination agreed between parties.');
});

test('terminating a lease removes its outstanding rent installments and keeps paid ones', function () {
    $lease = Lease::create([
        'property_id' => $this->rentalProperty->id,
        'agent_id' => $this->agent->id,
        'tenant_name' => 'Dawit Yilma',
        'tenant_email' => 'dawit@example.com',
        'tenant_phone' => '0933445566',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 30000.00,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);
    $lease->generateSchedules();

    $paidSchedule = $lease->schedules()->first();
    $paidSchedule->update([
        'status' => 'paid',
        'amount_paid' => $paidSchedule->amount_due,
        'paid_at' => Carbon::now(),
    ]);

    expect($lease->schedules()->count())->toBe(12);

    $this->actingAs($this->adminUser)
        ->put(route('leases.update-status', $lease->id), [
            'status' => 'terminated',
            'notes' => 'Tenancy ended early.',
        ])
        ->assertRedirect();

    $terminatedLease = $lease->fresh();

    expect($terminatedLease->schedules()->count())->toBe(1)
        ->and($terminatedLease->schedules()->where('status', 'paid')->count())->toBe(1)
        ->and($terminatedLease->outstanding_balance)->toBe(0.0);

    $this->assertDatabaseHas('rent_schedules', [
        'id' => $paidSchedule->id,
        'status' => 'paid',
    ]);
    $this->assertDatabaseMissing('rent_schedules', [
        'lease_id' => $lease->id,
        'status' => 'pending',
    ]);
});

test('recording a rent installment payment marks schedule as paid and syncs financial ledger', function () {
    $lease = Lease::create([
        'property_id' => $this->rentalProperty->id,
        'agent_id' => $this->agent->id,
        'tenant_name' => 'Hanna Tesfaye',
        'tenant_email' => 'hanna@example.com',
        'tenant_phone' => '0944556677',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 50000.00,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);
    $lease->generateSchedules();

    $schedule = $lease->schedules->first();

    $response = $this->actingAs($this->adminUser)
        ->post(route('rent-schedules.pay', $schedule->id), [
            'amount_paid' => 50000.00,
            'payment_method' => 'cbe_birr',
            'bank_transaction_reference' => 'CBE-FT-99887766',
            'notes' => 'Month 1 rent settled via CBE Birr',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $schedule->refresh();
    expect($schedule->status)->toBe('paid')
        ->and((float) $schedule->amount_paid)->toBe(50000.00)
        ->and($schedule->payment_id)->not->toBeNull()
        ->and($schedule->paid_at)->not->toBeNull();

    $payment = Payment::find($schedule->payment_id);
    expect($payment)->not->toBeNull()
        ->and($payment->payment_type)->toBe('rent_installment')
        ->and($payment->payment_method)->toBe('cbe_birr')
        ->and((float) $payment->amount)->toBe(50000.00)
        ->and($payment->status)->toBe('completed');

    $this->assertDatabaseHas('financial_ledgers', [
        'payment_id' => $payment->id,
        'category' => 'rental_revenue',
        'entry_type' => 'credit',
        'amount' => 50000.00,
    ]);
});

test('paying rent installment via Ethiopian payment engine marks schedule as paid', function () {
    $lease = Lease::create([
        'property_id' => $this->rentalProperty->id,
        'agent_id' => $this->agent->id,
        'tenant_name' => 'Ephrem Hailu',
        'tenant_email' => 'ephrem@example.com',
        'tenant_phone' => '0955667788',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 25000.00,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);
    $lease->generateSchedules();

    $schedule = $lease->schedules->first();

    // Instant telebirr payment simulation via PaymentController@store
    $response = $this->actingAs($this->adminUser)
        ->post(route('payments.store'), [
            'payable_type' => RentSchedule::class,
            'payable_id' => $schedule->id,
            'property_id' => $this->rentalProperty->id,
            'payer_name' => 'Ephrem Hailu',
            'payer_email' => 'ephrem@example.com',
            'payer_phone' => '0955667788',
            'payment_type' => 'rent_installment',
            'payment_method' => 'telebirr',
            'amount' => 25000.00,
            'notes' => 'Rent payment via Telebirr',
        ]);

    $response->assertRedirect();

    $schedule->refresh();
    expect($schedule->status)->toBe('paid')
        ->and((float) $schedule->amount_paid)->toBe(25000.00)
        ->and($schedule->payment_id)->not->toBeNull();

    $this->assertDatabaseHas('financial_ledgers', [
        'category' => 'rental_revenue',
        'amount' => 25000.00,
    ]);
});

test('admin dashboard renders leases desk and overdue notifications', function () {
    $lease = Lease::create([
        'property_id' => $this->rentalProperty->id,
        'agent_id' => $this->agent->id,
        'tenant_name' => 'Yonas Belay',
        'tenant_email' => 'yonas@example.com',
        'tenant_phone' => '0966778899',
        'start_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(10)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 30000.00,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);
    $lease->generateSchedules();

    // Mark one past installment overdue
    $overdueSchedule = $lease->schedules->first();
    $overdueSchedule->update([
        'due_date' => Carbon::now()->subMonth()->format('Y-m-d'),
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->adminUser)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Rental &amp; Lease Management Desk', false)
        ->assertSee('Yonas Belay')
        ->assertSee('Overdue Rent Installment(s) Pending Collection', false);
});

test('admin or agent can download official rental agreement PDF for a lease', function () {
    $lease = Lease::create([
        'property_id' => $this->rentalProperty->id,
        'agent_id' => $this->agent->id,
        'tenant_name' => 'Kenenisa Bekele',
        'tenant_email' => 'kenenisa@example.com',
        'tenant_phone' => '0911556677',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 60000.00,
        'security_deposit' => 120000.00,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);
    $lease->generateSchedules();

    $response = $this->actingAs($this->adminUser)
        ->get(route('leases.agreement', $lease->id));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toBe('application/pdf');
});

test('tenant can pay their rent installment from tenant workspace', function () {
    $tenantUser = User::factory()->create([
        'role' => 'tenant',
        'email' => 'my.tenant@example.com',
    ]);

    $lease = Lease::create([
        'property_id' => $this->rentalProperty->id,
        'agent_id' => $this->agent->id,
        'tenant_user_id' => $tenantUser->id,
        'tenant_name' => $tenantUser->name,
        'tenant_email' => $tenantUser->email,
        'tenant_phone' => '0911223344',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 35000.00,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);
    $lease->generateSchedules();

    $schedule = $lease->schedules->first();

    // 1. Tenant views dashboard with pay button
    $dashboardResponse = $this->actingAs($tenantUser)->get(route('tenant.dashboard'));
    $dashboardResponse->assertOk()
        ->assertSee('Pay 35,000 ETB')
        ->assertSee('Rent Schedule &amp; Payment History', false);

    // 2. Tenant pays their rent installment
    $response = $this->actingAs($tenantUser)
        ->post(route('rent-schedules.pay', $schedule->id), [
            'amount_paid' => 35000.00,
            'payment_method' => 'telebirr',
            'bank_transaction_reference' => 'TB-RENT-12345',
            'notes' => 'Month 1 rent paid via Telebirr',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $schedule->refresh();
    expect($schedule->status)->toBe('paid')
        ->and((float) $schedule->amount_paid)->toBe(35000.00)
        ->and($schedule->payment_id)->not->toBeNull()
        ->and($schedule->paid_at)->not->toBeNull()
        ->and($schedule->payment_method)->toBe('telebirr');

    $payment = Payment::find($schedule->payment_id);
    expect($payment)->not->toBeNull()
        ->and($payment->user_id)->toBe($tenantUser->id)
        ->and($payment->status)->toBe('completed')
        ->and((float) $payment->amount)->toBe(35000.00);

    $this->assertDatabaseHas('financial_ledgers', [
        'payment_id' => $payment->id,
        'category' => 'rental_revenue',
        'amount' => 35000.00,
    ]);

    // 3. Tenant pays installment 2 with default payload
    $schedule2 = $lease->schedules->get(1);
    $response2 = $this->actingAs($tenantUser)
        ->post(route('rent-schedules.pay', $schedule2->id));
    $response2->assertRedirect();
    $response2->assertSessionHas('success');
    expect($schedule2->fresh()->status)->toBe('paid')
        ->and((float) $schedule2->fresh()->amount_paid)->toBe(35000.00)
        ->and($schedule2->fresh()->payment_method)->toBe('telebirr');
});

test('tenant cannot pay rent installment for another tenants lease', function () {
    $tenantUser1 = User::factory()->create(['role' => 'tenant', 'email' => 'tenant1@example.com']);
    $tenantUser2 = User::factory()->create(['role' => 'tenant', 'email' => 'tenant2@example.com']);

    $lease = Lease::create([
        'property_id' => $this->rentalProperty->id,
        'agent_id' => $this->agent->id,
        'tenant_user_id' => $tenantUser2->id,
        'tenant_name' => 'Other Tenant',
        'tenant_email' => $tenantUser2->email,
        'tenant_phone' => '0922334455',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 20000.00,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);
    $lease->generateSchedules();

    $schedule = $lease->schedules->first();

    $response = $this->actingAs($tenantUser1)
        ->post(route('rent-schedules.pay', $schedule->id), [
            'amount_paid' => 20000.00,
            'payment_method' => 'telebirr',
        ]);

    $response->assertStatus(403);
});
