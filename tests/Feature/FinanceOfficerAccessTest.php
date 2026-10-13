<?php

use App\Models\Agent;
use App\Models\Lease;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'System Admin',
        'email' => 'admin@example.com',
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
        'name' => 'Selam Real Estate Agency',
        'email' => 'selam@example.com',
        'phone' => '+251 91 123 4567',
        'is_active' => true,
    ]);

    $this->property = Property::create([
        'property_code' => 'ETH-AA-APT-00000001',
        'title' => 'Bole Atlantic Apartment',
        'slug' => 'bole-atlantic-apartment',
        'price' => 5000000.00,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 180,
        'type' => 'rent',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanialem',
        'agent_id' => $this->agent->id,
    ]);

    $this->financeOfficer = User::create([
        'name' => 'Finance Officer Dawit',
        'email' => 'finance.dawit@example.com',
        'password' => Hash::make('password123'),
        'role' => 'finance_officer',
    ]);
});

test('admin can register a finance officer, agent and admin from the add user form', function () {
    $response = $this->actingAs($this->admin)->post(route('dashboard.users.store'), [
        'name' => 'Finance Officer Dawit',
        'email' => 'finance.new@example.com',
        'password' => 'Password123!',
        'role' => 'finance_officer',
    ]);

    $response->assertSessionHas('success');
    expect(User::where('email', 'finance.new@example.com')->first()->role)->toBe('finance_officer');

    $this->actingAs($this->admin)->post(route('dashboard.users.store'), [
        'name' => 'Agent New',
        'email' => 'agent.new@example.com',
        'password' => 'Password123!',
        'role' => 'agent',
    ]);
    $this->actingAs($this->admin)->post(route('dashboard.users.store'), [
        'name' => 'Admin New',
        'email' => 'admin.new@example.com',
        'password' => 'Password123!',
        'role' => 'admin',
    ]);

    expect(User::where('email', 'agent.new@example.com')->first()->role)->toBe('agent');
    expect(User::where('email', 'admin.new@example.com')->first()->role)->toBe('admin');
});

test('finance officer can operate the financial audit, tax, payments and ledger desk', function () {
    $response = $this->actingAs($this->financeOfficer)->get(route('finance.index'));

    $response->assertOk();
    $response->assertSee('Financial Reporting & Commission Settlement', false);
    $response->assertSee('Double-Entry Financial Ledgers', false);
    $response->assertSee('Lease & Rental Collection Desk', false);
});

test('finance officer can export financial statements and the audit statement', function () {
    $this->actingAs($this->financeOfficer)->get(route('finance.export-csv'))->assertOk();
    $this->actingAs($this->financeOfficer)->get(route('finance.statement'))->assertOk();
});

test('finance officer is denied the admin dashboard', function () {
    $this->actingAs($this->financeOfficer)->get(route('dashboard'))->assertForbidden();
});

test('finance officer cannot register a new lease agreement', function () {
    $response = $this->actingAs($this->financeOfficer)->post(route('leases.store'), [
        'property_id' => $this->property->id,
        'tenant_name' => 'Tenant Tigist',
        'tenant_email' => 'tigist@example.com',
        'tenant_phone' => '+251 91 333 4455',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 25000,
        'payment_frequency' => 'monthly',
    ]);

    $response->assertForbidden();
    expect(Lease::count())->toBe(0);
});

test('finance officer can manage existing lease status and agreements', function () {
    $lease = Lease::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'landlord_user_id' => $this->admin->id,
        'tenant_name' => 'Tenant Tigist',
        'tenant_email' => 'tigist@example.com',
        'tenant_phone' => '+251 91 333 4455',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addYear()->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 25000,
        'security_deposit' => 50000,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->financeOfficer)->put(route('leases.update-status', $lease), [
        'status' => 'pending_renewal',
        'notes' => 'Renewal notice sent',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect($lease->fresh()->status)->toBe('pending_renewal');

    $this->actingAs($this->financeOfficer)->get(route('leases.agreement', $lease))->assertOk();
});

test('finance officer lease desk renders rent installment schedules and overdue rent', function () {
    $lease = Lease::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'landlord_user_id' => $this->admin->id,
        'tenant_name' => 'Tenant Tigist',
        'tenant_email' => 'tigist@example.com',
        'tenant_phone' => '+251 91 333 4455',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addYear()->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 25000,
        'security_deposit' => 50000,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);
    $lease->generateSchedules();

    $overdueSchedule = $lease->schedules()->first();
    $overdueSchedule->update([
        'due_date' => now()->subMonth()->format('Y-m-d'),
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->financeOfficer)->get(route('finance.index'));

    $response->assertOk()
        ->assertSee('Rent Installment Schedules')
        ->assertSee('Overdue Rent')
        ->assertSee('Outstanding Balance')
        ->assertSee('Collect ETB')
        ->assertSee($overdueSchedule->period_label)
        ->assertSee('Tenant Tigist');
});

test('finance officer login redirects to the financial audit and tax desk', function () {
    $response = $this->post('/login', [
        'email' => 'finance.dawit@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/finance');
});
