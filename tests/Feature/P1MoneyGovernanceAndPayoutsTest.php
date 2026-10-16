<?php

use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\FinanceRate;
use App\Models\FinanceReviewFlag;
use App\Models\FinancialLedger;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\Refund;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\AgentBalanceService;
use App\Services\FinanceSettings;
use App\Services\LedgerService;
use App\Services\PayoutService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * P1 money governance: rate timing, idempotency, rollback safety, refund
 * recovery, VAT remittance, and the payout lifecycle (SRS §170, §171, §194).
 */
function govUser(string $role, string $suffix = ''): User
{
    return User::create([
        'name' => ucfirst($role).' '.$suffix,
        'email' => $role.'.'.$suffix.'.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => $role,
    ]);
}

function govProperty(array $attributes = []): Property
{
    return Property::create(array_merge([
        'property_code' => 'ETH-AA-GOV-'.strtoupper(Str::random(6)),
        'title' => 'Governance Test Property',
        'slug' => 'governance-test-'.Str::random(8),
        'type' => 'sale',
        'property_category' => 'apartment',
        'price' => 100000,
        'city' => 'Addis Ababa',
        'is_active' => true,
        'status' => 'published',
    ], $attributes));
}

// ── Check 1: rate timing ────────────────────────────────────────────────

test('a payment created before a rate change uses the rate snapshotted at creation', function () {
    $buyer = govUser('buyer', 'rate-timing');
    $admin = govUser('admin', 'rate-timing');

    // Created while the platform fee is 2%.
    $this->actingAs($buyer)->post(route('payments.store'), [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'bank_transfer',
        'amount' => 100000,
        'payer_name' => $buyer->name,
        'payer_email' => $buyer->email,
    ])->assertRedirect();

    $payment = Payment::latest('id')->first();

    // The rate changes before the payment is verified.
    SiteSetting::set(FinanceSettings::PLATFORM_FEE_RATE, '6');

    $this->actingAs($admin)->put(route('payments.verify', $payment), [])->assertRedirect();

    $payment->refresh();

    // The money is still the 2% that was snapshotted at creation, not 6%.
    expect((float) $payment->platform_fee)->toBe(2000.0)
        ->and((float) $payment->tax_amount)->toBe(300.0)
        ->and((float) $payment->platform_fee_rate)->toBe(2.0)
        ->and((float) $payment->net_amount)->toBe(97700.0);

    $feeLeg = FinancialLedger::where('transaction_group', 'payment:'.$payment->id)
        ->where('category', 'platform_fee')->first();

    expect((float) $feeLeg->amount)->toBe(2000.0)
        ->and((float) $feeLeg->rate_snapshot['rate'])->toBe(2.0);
});

// ── Check 2: idempotency ────────────────────────────────────────────────

test('the same idempotency key with the same payload returns the original payment', function () {
    $buyer = govUser('buyer', 'idem-same');

    $payload = [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => 15000,
        'payer_name' => $buyer->name,
        'payer_email' => $buyer->email,
    ];

    $this->actingAs($buyer)->withHeader('Idempotency-Key', 'gov-pay-1')->post(route('payments.store'), $payload)->assertRedirect();
    $this->actingAs($buyer)->withHeader('Idempotency-Key', 'gov-pay-1')->post(route('payments.store'), $payload)->assertRedirect();

    expect(Payment::where('idempotency_key', 'gov-pay-1')->count())->toBe(1)
        ->and(Payment::count())->toBe(1);
});

test('the same idempotency key with a different payload is rejected with a 409', function () {
    $buyer = govUser('buyer', 'idem-diff');

    $payload = [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => 15000,
        'payer_name' => $buyer->name,
        'payer_email' => $buyer->email,
    ];

    $this->actingAs($buyer)->withHeader('Idempotency-Key', 'gov-pay-2')->post(route('payments.store'), $payload)->assertRedirect();

    $this->actingAs($buyer)
        ->withHeader('Idempotency-Key', 'gov-pay-2')
        ->post(route('payments.store'), array_merge($payload, ['amount' => 9000]))
        ->assertStatus(409);

    expect(Payment::count())->toBe(1);
});

test('the same refund idempotency key with a different payload is rejected with a 409', function () {
    $finance = govUser('finance_officer', 'idem-refund');
    $payment = Payment::create([
        'payer_name' => 'Idem', 'payer_email' => 'idem@example.com',
        'payment_type' => 'earnest_deposit', 'payment_method' => 'bank_transfer',
        'amount' => 1000, 'platform_fee' => 20, 'tax_amount' => 3, 'status' => 'completed',
    ]);

    $this->actingAs($finance)->withHeader('Idempotency-Key', 'gov-refund-1')
        ->post(route('finance.payments.refund', $payment), ['refund_amount' => 400, 'reason' => 'first'])
        ->assertRedirect();

    $this->actingAs($finance)->withHeader('Idempotency-Key', 'gov-refund-1')
        ->post(route('finance.payments.refund', $payment), ['refund_amount' => 100, 'reason' => 'different'])
        ->assertStatus(409);

    expect((float) $payment->fresh()->refunded_amount)->toBe(400.0)
        ->and(Refund::count())->toBe(1);
});

// ── Check 3: rollback safety ────────────────────────────────────────────

test('refunding an offer that was already countered flags it for finance review', function () {
    $finance = govUser('finance_officer', 'rollback');
    $offer = PropertyOffer::factory()->create(['status' => 'countered']);

    $payment = Payment::create([
        'payable_type' => PropertyOffer::class,
        'payable_id' => $offer->id,
        'payer_name' => 'Buyer', 'payer_email' => 'buyer@example.com',
        'payment_type' => 'earnest_deposit', 'payment_method' => 'bank_transfer',
        'amount' => 25000, 'platform_fee' => 500, 'tax_amount' => 75, 'status' => 'completed',
    ]);

    $this->actingAs($finance)->post(route('finance.payments.refund', $payment), [
        'refund_amount' => 25000,
        'reason' => 'Deal fell through',
    ])->assertRedirect();

    // The countered state is preserved and a durable flag is raised instead.
    expect($offer->fresh()->status)->toBe('countered');

    $flag = FinanceReviewFlag::where('reviewable_id', $offer->id)
        ->where('reviewable_type', PropertyOffer::class)
        ->first();

    expect($flag)->not->toBeNull()
        ->and($flag->payment_id)->toBe($payment->id);
});

test('refunding an offer still under review rolls it back without a flag', function () {
    $finance = govUser('finance_officer', 'rollback-ok');
    $offer = PropertyOffer::factory()->create(['status' => 'under_review']);

    $payment = Payment::create([
        'payable_type' => PropertyOffer::class,
        'payable_id' => $offer->id,
        'payer_name' => 'Buyer', 'payer_email' => 'buyer@example.com',
        'payment_type' => 'earnest_deposit', 'payment_method' => 'bank_transfer',
        'amount' => 25000, 'platform_fee' => 500, 'tax_amount' => 75, 'status' => 'completed',
    ]);

    $this->actingAs($finance)->post(route('finance.payments.refund', $payment), [
        'refund_amount' => 25000,
        'reason' => 'Deal fell through',
    ])->assertRedirect();

    expect($offer->fresh()->status)->toBe('rejected')
        ->and(FinanceReviewFlag::count())->toBe(0);
});

// ── Check 4: refund after payout is a recovery ──────────────────────────

test('a refund after payout makes the agent balance negative (a recovery)', function () {
    $finance = govUser('finance_officer', 'recovery');
    $agentUser = govUser('agent', 'recovery');
    $agent = Agent::factory()->create(['user_id' => $agentUser->id]);
    $property = govProperty();

    $commission = Commission::create([
        'agent_id' => $agent->id,
        'property_id' => $property->id,
        'property_price' => 100000,
        'commission_rate' => 1.0,
        'commission_amount' => 1000,
        'status' => 'pending',
    ]);

    // Settle the commission: the agent earns 1,000 gross and is paid 980 net.
    $commission->settlePayout($finance, 'bank_transfer');

    $balances = app(AgentBalanceService::class);

    expect($balances->availableFor($agentUser))->toBe(0.0);

    // Refund the payment that funded the deal, after the payout.
    $payment = Payment::create([
        'property_id' => $property->id,
        'payer_name' => 'Buyer', 'payer_email' => 'buyer@example.com',
        'payment_type' => 'earnest_deposit', 'payment_method' => 'bank_transfer',
        'amount' => 100000, 'platform_fee' => 2000, 'tax_amount' => 300, 'status' => 'completed',
    ]);

    $this->actingAs($finance)->post(route('finance.payments.refund', $payment), [
        'refund_amount' => 100000,
        'reason' => 'Deal reversed',
    ])->assertRedirect();

    // The earning is reversed, so the balance is now the negative net paid out.
    expect($balances->availableFor($agentUser))->toBe(-980.0)
        ->and($balances->payoutableFor($agentUser))->toBe(0.0);

    // A payout request cannot be funded while the balance is negative.
    expect($balances->canRequest($agentUser, 500.0))->toBeFalse();
});

// ── Check 5: VAT report, remittance and credit notes ────────────────────

test('a VAT-reversing refund issues a credit note and the VAT report is reachable', function () {
    $finance = govUser('finance_officer', 'vat');
    $payment = Payment::create([
        'payer_name' => 'VAT', 'payer_email' => 'vat@example.com',
        'payment_type' => 'stay_booking', 'payment_method' => 'bank_transfer',
        'amount' => 10000, 'platform_fee' => 200, 'tax_amount' => 30, 'status' => 'completed',
    ]);

    $this->actingAs($finance)->post(route('finance.payments.refund', $payment), [
        'refund_amount' => 10000,
        'reason' => 'Full refund',
    ])->assertRedirect();

    $refund = Refund::latest('id')->first();

    expect($refund->credit_note_number)->not->toBeNull()
        ->and((float) $refund->vat_reversed)->toBe(30.0)
        ->and((float) FinancialLedger::where('category', 'vat_payable')->where('entry_type', 'debit')->sum('amount'))->toBe(30.0);

    $this->actingAs($finance)->get(route('finance.vat'))->assertOk()->assertSee('VAT PAYABLE REPORT');
});

test('recording a VAT remittance moves the vat_payable balance to the authority', function () {
    $admin = govUser('admin', 'vat-remit');

    Payment::create([
        'payer_name' => 'VAT', 'payer_email' => 'vat2@example.com',
        'payment_type' => 'stay_booking', 'payment_method' => 'bank_transfer',
        'amount' => 10000, 'platform_fee' => 200, 'tax_amount' => 30, 'status' => 'pending',
    ]);
    $payment = Payment::latest('id')->first();
    $this->actingAs($admin)->put(route('payments.verify', $payment), [])->assertRedirect();

    expect((float) FinancialLedger::where('category', 'vat_payable')->where('entry_type', 'credit')->sum('amount'))->toBe(30.0);

    $this->actingAs($admin)->post(route('finance.vat.remit'), [
        'amount' => 30,
        'period_start' => now()->startOfMonth()->toDateString(),
        'period_end' => now()->endOfMonth()->toDateString(),
        'reference' => 'MOR-VAT-0001',
    ])->assertRedirect();

    $debits = (float) FinancialLedger::where('category', 'vat_payable')->where('entry_type', 'debit')->sum('amount');
    $credits = (float) FinancialLedger::where('category', 'vat_payable')->where('entry_type', 'credit')->sum('amount');

    expect($debits)->toBe(30.0)
        ->and($credits - $debits)->toBe(0.0);

    $this->assertDatabaseHas('financial_ledgers', [
        'category' => 'cash', 'entry_type' => 'credit', 'amount' => 30.00,
    ]);
});

// ── Check 8: rate-change logging and second approval ────────────────────

test('a rate change logs the old and new values', function () {
    $finance = govUser('finance_officer', 'rate-log');
    SiteSetting::set(FinanceSettings::PLATFORM_FEE_RATE, '2');

    $this->actingAs($finance)->post(route('finance.settings.rates.store'), [
        'key' => FinanceRate::PLATFORM_FEE,
        'rate' => 4,
        'effective_from' => now()->toDateString(),
        'reason' => 'New platform fee policy',
    ])->assertRedirect();

    $log = AuditLog::where('action', 'finance.rate_updated')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->old_value)->toMatchArray(['key' => FinanceRate::PLATFORM_FEE, 'rate' => 2.0])
        ->and($log->new_value['rate'])->toEqual(4.0)
        ->and($log->new_value['previous_rate'])->toEqual(2.0);
});

test('with second approval on, a rate change needs a different approver', function () {
    $maker = govUser('finance_officer', 'maker');
    $checker = govUser('finance_officer', 'checker');
    SiteSetting::set(FinanceSettings::REQUIRE_SECOND_APPROVAL, '1');

    $this->actingAs($maker)->post(route('finance.settings.rates.store'), [
        'key' => FinanceRate::PLATFORM_FEE,
        'rate' => 7,
        'effective_from' => now()->toDateString(),
        'reason' => 'Pending policy change',
    ])->assertRedirect();

    $rate = FinanceRate::latest('id')->first();

    expect($rate->status)->toBe(FinanceRate::STATUS_PENDING);

    // A pending row never moves money.
    expect(FinanceRate::rateFor(FinanceRate::PLATFORM_FEE))->not->toBe(7.0);

    // The maker cannot approve their own change.
    $this->actingAs($maker)
        ->post(route('finance.settings.rates.approve', $rate))
        ->assertForbidden();

    // A different finance user can.
    $this->actingAs($checker)
        ->post(route('finance.settings.rates.approve', $rate))
        ->assertRedirect();

    expect($rate->fresh()->status)->toBe(FinanceRate::STATUS_APPROVED)
        ->and(FinanceRate::rateFor(FinanceRate::PLATFORM_FEE))->toBe(7.0);
});

// ── Batch C: payout lifecycle ───────────────────────────────────────────

test('a payout request must clear the minimum threshold and the available balance', function () {
    $agentUser = govUser('agent', 'payout-threshold');
    $agent = Agent::factory()->create(['user_id' => $agentUser->id]);
    $finance = govUser('finance_officer', 'payout-threshold');
    $property = govProperty();

    $commission = Commission::create([
        'agent_id' => $agent->id, 'property_id' => $property->id,
        'property_price' => 100000, 'commission_rate' => 1.0, 'commission_amount' => 1000, 'status' => 'pending',
    ]);
    $commission->settlePayout($finance, 'bank_transfer');

    // The agent's balance is 0 (paid out), so a request is refused.
    $this->actingAs($agentUser)->post(route('finance.payouts.store'), [
        'gross_amount' => 100, 'payment_method' => 'telebirr',
    ])->assertStatus(422);
});

test('the maker-checker rule stops a requester approving their own payout', function () {
    $finance = govUser('finance_officer', 'maker-checker');
    $party = govUser('host', 'maker-checker');

    $payout = PayoutRequest::create([
        'payout_code' => 'PAY-REQ-MC-01', 'user_id' => $party->id, 'role' => 'host',
        'gross_amount' => 20000, 'withholding_tax' => 400, 'net_amount' => 19600,
        'payment_method' => 'telebirr', 'status' => 'pending',
    ]);

    // The requester cannot approve their own payout.
    $this->actingAs($party)->patch(route('finance.payouts.process', $payout), ['decision' => 'approve'])
        ->assertForbidden();

    // A different finance user can.
    $this->actingAs($finance)->patch(route('finance.payouts.process', $payout), ['decision' => 'approve'])
        ->assertRedirect();

    expect($payout->fresh()->status)->toBe('approved')
        ->and($payout->fresh()->approved_by)->toBe($finance->id);
});

test('disbursing a payout issues a voucher, records the bank reference, and is idempotent', function () {
    $finance = govUser('finance_officer', 'disburse');
    $party = govUser('host', 'disburse');

    $payout = PayoutRequest::create([
        'payout_code' => 'PAY-REQ-DISB-01', 'user_id' => $party->id, 'role' => 'host',
        'gross_amount' => 20000, 'withholding_tax' => 400, 'net_amount' => 19600,
        'payment_method' => 'telebirr', 'status' => 'pending',
    ]);

    $this->actingAs($finance)->patch(route('finance.payouts.process', $payout), ['decision' => 'approve'])->assertRedirect();
    $this->actingAs($finance)->withHeader('Idempotency-Key', 'gov-disb-1')
        ->patch(route('finance.payouts.process', $payout), ['decision' => 'disburse', 'transaction_reference' => 'FT-9911'])
        ->assertRedirect();

    $payout->refresh();

    expect($payout->status)->toBe('disbursed')
        ->and($payout->voucher_number)->toStartWith('VCH-PAY-')
        ->and($payout->transaction_reference)->toBe('FT-9911')
        ->and($payout->isManualDisbursement())->toBeTrue();

    // Replaying the disbursement does not post a second journal.
    $this->actingAs($finance)->patch(route('finance.payouts.process', $payout), ['decision' => 'disburse'])
        ->assertRedirect();

    $legs = FinancialLedger::where('transaction_group', 'payout:'.$payout->id)->get();

    expect($legs->where('entry_type', 'debit')->sum('amount'))->toEqual(20000.0)
        ->and($legs->where('entry_type', 'credit')->sum('amount'))->toEqual(20000.0)
        ->and($party->fresh()->notifications()->count())->toBeGreaterThan(0);
});

test('the payout schedule can close payouts', function () {
    $party = govUser('host', 'schedule');
    SiteSetting::set(FinanceSettings::PAYOUT_SCHEDULE, 'monthly');

    $service = app(PayoutService::class);

    // Monthly payouts are open on the 1st and closed on any other day.
    expect($service->scheduleAllows($party, now()->startOfMonth()))->toBeTrue()
        ->and($service->scheduleAllows($party, now()->startOfMonth()->addDay()))->toBeFalse();
});

test('a holdback reduces the payoutable balance', function () {
    $agentUser = govUser('agent', 'holdback');
    $agent = Agent::factory()->create(['user_id' => $agentUser->id]);
    $finance = govUser('finance_officer', 'holdback');
    $property = govProperty();

    $commission = Commission::create([
        'agent_id' => $agent->id, 'property_id' => $property->id,
        'property_price' => 100000, 'commission_rate' => 10.0, 'commission_amount' => 10000, 'status' => 'pending',
    ]);
    // Post the earning only (the payable side is still outstanding), attributed
    // to the agent, so the party has a positive ledger-derived balance.
    app(LedgerService::class)->post('commission:'.$commission->id, [
        ['entry_type' => 'debit', 'category' => 'agent_commission', 'amount' => 10000, 'currency' => 'ETB', 'description' => 'Earned', 'user_id' => $finance->id, 'party_user_id' => $agentUser->id],
        ['entry_type' => 'credit', 'category' => 'agent_payable', 'amount' => 10000, 'currency' => 'ETB', 'description' => 'Payable', 'user_id' => $finance->id, 'party_user_id' => $agentUser->id],
    ]);

    $balances = app(AgentBalanceService::class);

    SiteSetting::set(FinanceSettings::PAYOUT_HOLDBACK_RATE, '10');

    // 10,000 earned, 10% held back = 9,000 payoutable.
    expect($balances->availableFor($agentUser))->toBe(9000.0)
        ->and($balances->heldFor($agentUser))->toBe(1000.0);
});

test('a bank reference can be matched to settle a pending payment', function () {
    $finance = govUser('finance_officer', 'recon');

    Payment::create([
        'payer_name' => 'Recon', 'payer_email' => 'recon@example.com',
        'payment_type' => 'earnest_deposit', 'payment_method' => 'bank_transfer',
        'bank_transaction_reference' => 'CBE-REF-777',
        'amount' => 5000, 'platform_fee' => 100, 'tax_amount' => 15, 'status' => 'pending',
    ]);

    $payment = Payment::latest('id')->first();

    $this->actingAs($finance)->post(route('finance.reconciliation.match'), [
        'reference' => 'CBE-REF-777',
    ])->assertRedirect();

    expect($payment->fresh()->status)->toBe('completed')
        ->and(FinancialLedger::where('transaction_group', 'payment:'.$payment->id)->exists())->toBeTrue();

    $this->actingAs($finance)->get(route('finance.reconciliation'))->assertOk();
});
