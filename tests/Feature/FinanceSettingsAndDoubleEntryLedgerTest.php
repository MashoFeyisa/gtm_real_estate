<?php

use App\Models\Agent;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\FinanceRate;
use App\Models\FinancialLedger;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\FinanceSettings;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');

    $this->admin = User::create([
        'name' => 'Finance Admin Ledger',
        'email' => 'ledger.admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->agentUser = User::create([
        'name' => 'Agent Ledger',
        'email' => 'ledger.agent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Ledger Realty',
        'email' => 'ledger.realty@example.com',
        'phone' => '+251 91 000 0000',
        'is_active' => true,
    ]);

    $this->buyer = User::create([
        'name' => 'Buyer Ledger',
        'email' => 'ledger.buyer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->rentProperty = Property::create([
        'property_code' => 'ETH-AA-LED-00000001',
        'title' => 'Ledger Test Rental Villa',
        'slug' => 'ledger-test-rental-villa',
        'price' => 100000.00,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 200,
        'building_area' => 200,
        'type' => 'rent',
        'property_category' => 'villa',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole, Addis Ababa',
        'agent_id' => $this->agent->id,
    ]);
});

test('platform fee, VAT and net amount are read from database settings', function () {
    SiteSetting::set(FinanceSettings::PLATFORM_FEE_RATE, '4');
    SiteSetting::set(FinanceSettings::VAT_RATE, '10');

    $this->actingAs($this->buyer)->post(route('payments.store'), [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => 100000,
        'payer_name' => 'Buyer Ledger',
        'payer_email' => 'ledger.buyer@example.com',
    ])->assertRedirect();

    $payment = Payment::latest('id')->first();

    expect($payment)->not->toBeNull()
        ->and((float) $payment->platform_fee)->toBe(4000.0)
        ->and((float) $payment->tax_amount)->toBe(400.0)
        // VAT is charged on top of the fee, so it comes out of the beneficiary's
        // net: 100,000 - 4,000 fee - 400 VAT.
        ->and((float) $payment->net_amount)->toBe(95600.0);
});

test('the net amount formula is identical wherever a payment is built', function () {
    $settings = app(FinanceSettings::class);

    $payment = Payment::create([
        'payer_name' => 'Direct Payer',
        'payer_email' => 'direct@example.com',
        'payment_method' => 'cash',
        'payment_type' => 'service_fee',
        'amount' => 10000,
        'platform_fee' => 250,
        'tax_amount' => 37.5,
        'currency' => 'ETB',
        'status' => 'pending',
    ]);

    expect((float) $payment->net_amount)->toBe(9712.5)
        ->and((float) $payment->net_amount)->toBe($settings->netAmount(10000, 250, 37.5));
});

test('withholding tax and its ledger entry come from the settings rate', function () {
    SiteSetting::set(FinanceSettings::WITHHOLDING_TAX_RATE, '5');

    $commission = Commission::create([
        'agent_id' => $this->agent->id,
        'property_id' => $this->rentProperty->id,
        'property_price' => 1000000,
        'commission_rate' => 10.0,
        'commission_amount' => 100000,
        'status' => 'pending',
    ]);

    $this->actingAs($this->admin)
        ->post(route('commissions.settle', $commission), ['payout_method' => 'bank_transfer'])
        ->assertRedirect();

    $commission->refresh();

    expect((float) $commission->withholding_tax_rate)->toBe(5.0)
        ->and((float) $commission->withholding_tax_amount)->toBe(5000.0)
        ->and((float) $commission->net_payout_amount)->toBe(95000.0);

    $this->assertDatabaseHas('financial_ledgers', [
        'transaction_group' => 'commission:'.$commission->id,
        'category' => 'tax_withholding',
        'entry_type' => 'credit',
        'amount' => 5000.00,
    ]);
});

test('rental commission uses the configurable rate instead of a full month of rent', function () {
    SiteSetting::set(FinanceSettings::DEFAULT_COMMISSION_RATE, '3');

    $offer = PropertyOffer::create([
        'property_id' => $this->rentProperty->id,
        'agent_id' => $this->agent->id,
        'buyer_name' => 'Renter Ledger',
        'buyer_email' => 'renter@example.com',
        'buyer_phone' => '0911000000',
        'offer_type' => 'rental',
        'original_price' => 100000.00,
        'offered_price' => 100000.00,
        'current_counter_amount' => 100000.00,
        'deposit_proposed' => 200000.00,
        'status' => 'accepted',
    ]);

    $this->actingAs($this->admin)
        ->post(route('offers.convert-to-deal', $offer))
        ->assertRedirect();

    $commission = Commission::where('property_id', $this->rentProperty->id)->first();

    expect($commission)->not->toBeNull()
        ->and((float) $commission->commission_rate)->toBe(3.0)
        ->and((float) $commission->commission_amount)->toBe(3000.0);
});

test('every ledger transaction balances its debits and credits', function () {
    // 1. A client payment is submitted and verified by finance.
    $this->actingAs($this->buyer)->post(route('payments.store'), [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => 50000,
        'payer_name' => 'Buyer Ledger',
        'payer_email' => 'ledger.buyer@example.com',
    ])->assertRedirect();

    $payment = Payment::latest('id')->first();
    $this->actingAs($this->admin)->put(route('payments.verify', $payment), [])->assertRedirect();

    // 2. An agent commission is settled with withholding tax.
    $commission = Commission::create([
        'agent_id' => $this->agent->id,
        'property_id' => $this->rentProperty->id,
        'property_price' => 800000,
        'commission_rate' => 10.0,
        'commission_amount' => 80000,
        'status' => 'pending',
    ]);
    $this->actingAs($this->admin)
        ->post(route('commissions.settle', $commission), ['payout_method' => 'bank_transfer'])
        ->assertRedirect();

    // 3. Part of the payment is refunded.
    $this->actingAs($this->admin)->post(route('finance.payments.refund', $payment), [
        'refund_amount' => 10000,
        'reason' => 'Overpayment correction',
    ])->assertRedirect();

    // 4. A host payout request is disbursed.
    $payout = PayoutRequest::create([
        'payout_code' => 'PAY-REQ-LEDGER-01',
        'user_id' => $this->agentUser->id,
        'role' => 'agent',
        'gross_amount' => 20000,
        'withholding_tax' => 400,
        'net_amount' => 19600,
        'payment_method' => 'telebirr',
        'status' => 'pending',
    ]);
    $this->actingAs($this->admin)
        ->patch(route('finance.payouts.process', $payout), ['decision' => 'approve'])
        ->assertRedirect();
    $this->actingAs($this->admin)
        ->patch(route('finance.payouts.process', $payout), ['decision' => 'disburse'])
        ->assertRedirect();

    $unbalanced = FinancialLedger::query()
        ->whereNotNull('transaction_group')
        ->get()
        ->groupBy('transaction_group')
        ->filter(fn ($legs) => round((float) $legs->where('entry_type', 'debit')->sum('amount'), 2)
            !== round((float) $legs->where('entry_type', 'credit')->sum('amount'), 2))
        ->keys();

    expect($unbalanced->all())->toBe([])
        ->and(FinancialLedger::whereNotNull('transaction_group')->count())->toBeGreaterThan(0);
});

/**
 * Submit and verify a payment as finance, returning the settled payment.
 */
function settledPayment(float $amount): Payment
{
    $buyer = User::create([
        'name' => 'VAT Buyer',
        'email' => 'vat.buyer.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    test()->actingAs($buyer)->post(route('payments.store'), [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => $amount,
        'payer_name' => 'VAT Buyer',
        'payer_email' => $buyer->email,
    ])->assertRedirect();

    $payment = Payment::latest('id')->first();

    $admin = User::create([
        'name' => 'VAT Verifier',
        'email' => 'vat.verifier.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    test()->actingAs($admin)->put(route('payments.verify', $payment), [])->assertRedirect();

    return $payment->fresh();
}

/**
 * The debit and credit totals of one transaction group.
 *
 * @return array{0: float, 1: float}
 */
function groupTotals(string $group): array
{
    $legs = FinancialLedger::where('transaction_group', $group)->get();

    return [
        round((float) $legs->where('entry_type', 'debit')->sum('amount'), 2),
        round((float) $legs->where('entry_type', 'credit')->sum('amount'), 2),
    ];
}

test('VAT on the platform fee is posted to its own payable account and balances', function () {
    $payment = settledPayment(50000);

    // 50,000 cash in; 1,000 fee (2%); 150 VAT on the fee (15%); 48,850 to the beneficiary.
    expect((float) $payment->platform_fee)->toBe(1000.0)
        ->and((float) $payment->tax_amount)->toBe(150.0)
        ->and((float) $payment->net_amount)->toBe(48850.0);

    $this->assertDatabaseHas('financial_ledgers', [
        'transaction_group' => 'payment:'.$payment->id,
        'category' => 'vat_payable',
        'entry_type' => 'credit',
        'amount' => 150.00,
    ]);

    [$debits, $credits] = groupTotals('payment:'.$payment->id);

    expect($debits)->toBe(50000.0)
        ->and($credits)->toBe(50000.0);
});

test('the rate that produced a payment is snapshotted and never recalculated', function () {
    SiteSetting::set(FinanceSettings::PLATFORM_FEE_RATE, '4');
    SiteSetting::set(FinanceSettings::VAT_RATE, '10');

    $payment = settledPayment(100000);

    expect((float) $payment->platform_fee_rate)->toBe(4.0)
        ->and((float) $payment->vat_rate)->toBe(10.0)
        ->and((float) $payment->platform_fee)->toBe(4000.0)
        ->and((float) $payment->tax_amount)->toBe(400.0)
        ->and((float) $payment->net_amount)->toBe(95600.0);

    // A later rate change must not touch the record or its journal.
    SiteSetting::set(FinanceSettings::PLATFORM_FEE_RATE, '6');
    SiteSetting::set(FinanceSettings::VAT_RATE, '20');

    $payment->refresh();
    expect((float) $payment->platform_fee)->toBe(4000.0)
        ->and((float) $payment->tax_amount)->toBe(400.0)
        ->and((float) $payment->net_amount)->toBe(95600.0)
        ->and((float) $payment->platform_fee_rate)->toBe(4.0);

    $this->assertDatabaseHas('financial_ledgers', [
        'transaction_group' => 'payment:'.$payment->id,
        'category' => 'platform_fee',
        'amount' => 4000.00,
    ]);
});

test('a dated finance rate only applies from its effective date', function () {
    FinanceRate::create([
        'key' => FinanceRate::PLATFORM_FEE,
        'rate' => 3.0,
        'effective_from' => '2020-01-01',
    ]);

    $settings = app(FinanceSettings::class);

    // A future rule is not live yet, so today still resolves to the 2020 rule.
    FinanceRate::create([
        'key' => FinanceRate::PLATFORM_FEE,
        'rate' => 9.0,
        'effective_from' => now()->addDay()->toDateString(),
    ]);

    expect($settings->platformFeeRate())->toBe(3.0)
        ->and($settings->platformFeeRate(now()->addDays(2)))->toBe(9.0);
});

test('commission rules override the default by agent, property type and company', function () {
    $settings = app(FinanceSettings::class);

    // No rule: the shared default.
    expect($settings->commissionRateFor(null, 'villa'))->toBe(5.0);

    CommissionRule::create([
        'property_type' => 'villa',
        'rate' => 3.0,
        'effective_from' => '2020-01-01',
    ]);
    expect($settings->commissionRateFor(null, 'villa'))->toBe(3.0)
        ->and($settings->commissionRateFor(null, 'apartment'))->toBe(5.0);

    // An agent-specific rule is more specific than the property type.
    CommissionRule::create([
        'agent_id' => $this->agent->id,
        'rate' => 7.0,
        'effective_from' => '2020-01-01',
    ]);
    expect($settings->commissionRateFor($this->agent->id, 'villa'))->toBe(7.0);

    // A company rule applies when neither agent nor property type matches.
    CommissionRule::create([
        'company_id' => 42,
        'rate' => 4.0,
        'effective_from' => '2020-01-01',
    ]);
    expect($settings->commissionRateFor(null, null, 42))->toBe(4.0);
});

test('money keeps two-decimal precision and 66,666.67 ETB still balances', function () {
    $payment = settledPayment(66666.67);

    // 66,666.67 * 2% = 1,333.33 (half-up); VAT 15% of that = 200.00;
    // the beneficiary keeps 66,666.67 - 1,333.33 - 200.00 = 65,133.34.
    expect((float) $payment->platform_fee)->toBe(1333.33)
        ->and((float) $payment->tax_amount)->toBe(200.00)
        ->and((float) $payment->net_amount)->toBe(65133.34);

    [$debits, $credits] = groupTotals('payment:'.$payment->id);

    expect($debits)->toBe(66666.67)
        ->and($credits)->toBe(66666.67);
});

test('a full and a partial refund reverse the fee and VAT proportionally and balance', function () {
    $payment = settledPayment(10000);
    $admin = User::create([
        'name' => 'Refund Admin',
        'email' => 'refund.admin.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    // Partial refund of 4,000: reverses 40% of the 200 fee and 30 VAT.
    $this->actingAs($admin)->post(route('finance.payments.refund', $payment), [
        'refund_amount' => 4000,
        'reason' => 'Partial cancellation',
    ])->assertRedirect();

    $partial = FinancialLedger::where('payment_id', $payment->id)
        ->where('category', 'platform_fee')->where('entry_type', 'debit')->sum('amount');
    $partialVat = FinancialLedger::where('payment_id', $payment->id)
        ->where('category', 'vat_payable')->where('entry_type', 'debit')->sum('amount');

    expect((float) $partial)->toBe(80.0)
        ->and((float) $partialVat)->toBe(12.0);

    // Full refund of the remaining 6,000 reverses the rest of the fee and VAT.
    $this->actingAs($admin)->post(route('finance.payments.refund', $payment->fresh()), [
        'refund_amount' => 6000,
        'reason' => 'Full cancellation',
    ])->assertRedirect();

    expect((float) FinancialLedger::where('payment_id', $payment->id)
        ->where('category', 'platform_fee')->where('entry_type', 'debit')->sum('amount'))->toBe(200.0)
        ->and((float) FinancialLedger::where('payment_id', $payment->id)
            ->where('category', 'vat_payable')->where('entry_type', 'debit')->sum('amount'))->toBe(30.0);

    $unbalanced = FinancialLedger::where('payment_id', $payment->id)
        ->whereNotNull('transaction_group')
        ->get()
        ->groupBy('transaction_group')
        ->filter(fn ($legs) => round((float) $legs->where('entry_type', 'debit')->sum('amount'), 2)
            !== round((float) $legs->where('entry_type', 'credit')->sum('amount'), 2));

    expect($unbalanced->keys()->all())->toBe([]);
});
