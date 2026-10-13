<?php

use App\Models\Agent;
use App\Models\Commission;
use App\Models\FinancialLedger;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'System Admin',
        'email' => 'admin@realestate.com',
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
        'type' => 'sale',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanialem',
        'agent_id' => $this->agent->id,
    ]);
});

test('guests are redirected to login when accessing financial reporting', function () {
    $response = $this->get(route('finance.index'));

    $response->assertRedirect(route('login'));
});

test('regular clients cannot access financial reporting', function () {
    $client = User::create([
        'name' => 'Abebe Client',
        'email' => 'abebe@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $response = $this->actingAs($client)->get(route('finance.index'));

    $response->assertForbidden();
});

test('admin can view financial reporting desk with KPI stats and ledger breakdown', function () {
    // Seed sample financial ledger entries
    FinancialLedger::create([
        'entry_type' => 'credit',
        'category' => 'rental_income',
        'amount' => 45000.00,
        'currency' => 'ETB',
        'description' => 'Monthly rental collection for Bole Villa',
        'reference' => 'LSE-2026-001-PAY1',
        'user_id' => $this->admin->id,
    ]);

    FinancialLedger::create([
        'entry_type' => 'credit',
        'category' => 'platform_fee',
        'amount' => 15000.00,
        'currency' => 'ETB',
        'description' => 'Platform brokerage service fee',
        'reference' => 'FEE-REF-001',
        'user_id' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('finance.index'));

    $response->assertOk();
    $response->assertSee('Financial Reporting & Commission Settlement', false);
    $response->assertSee('45,000.00');
    $response->assertSee('15,000.00');
    $response->assertSee('LSE-2026-001-PAY1');
});

test('admin can filter financial reporting by period', function () {
    $response = $this->actingAs($this->admin)->get(route('finance.index', ['filter' => 'month']));

    $response->assertOk();
    $response->assertSee('This Month');
});

test('admin can settle pending agent commission with 2% withholding tax and ledger debit', function () {
    $commission = Commission::create([
        'agent_id' => $this->agent->id,
        'property_id' => $this->property->id,
        'property_price' => 5000000.00,
        'commission_rate' => 2.0,
        'commission_amount' => 100000.00,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)->post(route('commissions.settle', $commission), [
        'payout_method' => 'cbe_birr',
        'payout_reference' => 'CBE-TX-998877',
        'withholding_tax_rate' => 2.0,
        'admin_note' => 'Processed via Commercial Bank of Ethiopia branch transfer.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $commission->refresh();

    expect($commission->status)->toBe('paid')
        ->and($commission->withholding_tax_rate)->toBe(2.0)
        ->and($commission->withholding_tax_amount)->toBe(2000.00)
        ->and($commission->net_payout_amount)->toBe(98000.00)
        ->and($commission->payout_method)->toBe('cbe_birr')
        ->and($commission->payout_reference)->toBe('CBE-TX-998877')
        ->and($commission->disbursed_by)->toBe($this->admin->id)
        ->and($commission->voucher_number)->toStartWith('VCH-COMM-');

    // Verify Financial Ledger debit entry
    $ledger = FinancialLedger::where('category', 'agent_commission')->latest()->first();
    expect($ledger)->not->toBeNull()
        ->and($ledger->entry_type)->toBe('debit')
        ->and((float) $ledger->amount)->toBe(100000.00)
        ->and($ledger->currency)->toBe('ETB');
});

test('agent can view their official commission disbursement voucher', function () {
    $commission = Commission::create([
        'agent_id' => $this->agent->id,
        'property_id' => $this->property->id,
        'property_price' => 2000000.00,
        'commission_rate' => 2.0,
        'commission_amount' => 40000.00,
        'withholding_tax_rate' => 2.0,
        'withholding_tax_amount' => 800.00,
        'net_payout_amount' => 39200.00,
        'payout_method' => 'telebirr',
        'payout_reference' => 'TLB-883311',
        'disbursed_by' => $this->admin->id,
        'voucher_number' => 'VCH-COMM-2026-TEST01',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $response = $this->actingAs($this->agentUser)->get(route('commissions.voucher', $commission));

    $response->assertOk();
    $response->assertSee('AGENT COMMISSION DISBURSEMENT VOUCHER', false);
    $response->assertSee('VCH-COMM-2026-TEST01');
    $response->assertSee('39,200.00');
    $response->assertSee('800.00');
    $response->assertSee('TLB-883311');
});

test('unauthorized client cannot view another agents commission voucher', function () {
    $commission = Commission::create([
        'agent_id' => $this->agent->id,
        'property_id' => $this->property->id,
        'property_price' => 2000000.00,
        'commission_rate' => 2.0,
        'commission_amount' => 40000.00,
        'withholding_tax_rate' => 2.0,
        'withholding_tax_amount' => 800.00,
        'net_payout_amount' => 39200.00,
        'voucher_number' => 'VCH-COMM-2026-TEST02',
        'status' => 'paid',
    ]);

    $client = User::create([
        'name' => 'Unauthorized Client',
        'email' => 'other@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $response = $this->actingAs($client)->get(route('commissions.voucher', $commission));

    $response->assertForbidden();
});

test('admin can view any commission voucher', function () {
    $commission = Commission::create([
        'agent_id' => $this->agent->id,
        'property_id' => $this->property->id,
        'property_price' => 2000000.00,
        'commission_rate' => 2.0,
        'commission_amount' => 40000.00,
        'status' => 'paid',
        'voucher_number' => 'VCH-COMM-2026-TEST03',
    ]);

    $response = $this->actingAs($this->admin)->get(route('commissions.voucher', $commission));

    $response->assertOk();
    $response->assertSee('VCH-COMM-2026-TEST03');
});

test('admin can export financial ledgers to CSV', function () {
    FinancialLedger::create([
        'entry_type' => 'credit',
        'category' => 'rental_income',
        'amount' => 50000.00,
        'currency' => 'ETB',
        'description' => 'Auditable export test',
        'reference' => 'CSV-TEST-001',
        'user_id' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('finance.export-csv'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('content-disposition'))->toContain('GTM-Financial-Ledgers-');
});

test('admin can view printable financial audit statement', function () {
    $response = $this->actingAs($this->admin)->get(route('finance.statement'));

    $response->assertOk();
    $response->assertSee('OFFICIAL FINANCIAL AUDIT STATEMENT', false);
    $response->assertSee('GTM Real Estate');
});

test('agent can view commissions desk in their agent portal', function () {
    Commission::create([
        'agent_id' => $this->agent->id,
        'property_id' => $this->property->id,
        'property_price' => 3000000.00,
        'commission_rate' => 2.0,
        'commission_amount' => 60000.00,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->agentUser)->get(route('agent.portal'));

    $response->assertOk();
    $response->assertSee('My Earned Commissions &amp; Payouts', false);
    $response->assertSee('ERCA 2% WHT Compliant');
    $response->assertSee('60,000.00');
});
