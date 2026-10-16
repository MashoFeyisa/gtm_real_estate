<?php

use App\Models\Agent;
use App\Models\Commission;
use App\Models\FinanceReviewFlag;
use App\Models\FinancialLedger;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\Property;
use App\Models\User;
use App\Services\AgentBalanceService;
use App\Services\LedgerService;
use App\Services\PayoutService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * P1 Batch C follow-up checks: concurrency, backfill, hosts/professionals,
 * access control, negative balances, and reconciliation (SRS §83, §194).
 */
function fuUser(string $role, string $suffix = ''): User
{
    return User::create([
        'name' => ucfirst($role).' '.$suffix,
        'email' => $role.'.'.$suffix.'.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => $role,
    ]);
}

function fuProperty(array $attributes = []): Property
{
    return Property::create(array_merge([
        'property_code' => 'ETH-AA-FU-'.strtoupper(Str::random(6)),
        'title' => 'Follow-up Test Property',
        'slug' => 'follow-up-test-'.Str::random(8),
        'type' => 'sale',
        'property_category' => 'apartment',
        'price' => 100000,
        'city' => 'Addis Ababa',
        'is_active' => true,
        'status' => 'published',
    ], $attributes));
}

/**
 * Post an earning-only commission journal attributed to a party, leaving a
 * positive ledger-derived balance (no payout side yet).
 */
function fuEarn(User $party, float $gross, string $group): void
{
    app(LedgerService::class)->post($group, [
        ['entry_type' => 'debit', 'category' => 'agent_commission', 'amount' => $gross, 'currency' => 'ETB', 'description' => 'Earned', 'user_id' => $party->id, 'party_user_id' => $party->id],
        ['entry_type' => 'credit', 'category' => 'agent_payable', 'amount' => $gross, 'currency' => 'ETB', 'description' => 'Payable', 'user_id' => $party->id, 'party_user_id' => $party->id],
    ]);
}

// ── Check 1: concurrency ────────────────────────────────────────────────

test('two payout requests that together exceed the balance leave only one', function () {
    $party = fuUser('agent', 'concurrency');
    fuEarn($party, 1000, 'commission:concurrency-1');

    $balances = app(AgentBalanceService::class);
    expect($balances->payoutableFor($party))->toBe(1000.0);

    // Each request alone fits, but together (1600) they exceed the 1,000
    // balance. The second must fail, whatever order they are attempted in.
    $outcomes = [];

    foreach ([800, 800] as $amount) {
        try {
            app(PayoutService::class)->request($party, $amount, 'telebirr', null, null, 'agent');
            $outcomes[] = 'ok';
        } catch (HttpException $exception) {
            $outcomes[] = $exception->getStatusCode();
        }
    }

    expect(array_filter($outcomes, fn ($outcome) => $outcome === 'ok'))->toHaveCount(1)
        ->and(PayoutRequest::where('user_id', $party->id)->count())->toBe(1)
        ->and((float) PayoutRequest::where('user_id', $party->id)->sum('gross_amount'))->toBe(800.0);
});

test('the payout request balance check runs inside a locking transaction', function () {
    $source = file_get_contents(app_path('Services/PayoutService.php'));

    // The request path must open a transaction, lock the party row, and only
    // then read the balance.
    expect($source)->toContain('DB::transaction')
        ->toContain('lockForUpdate')
        ->and(strpos($source, 'lockForUpdate'))->toBeLessThan(strpos($source, 'payoutableFor($party)'));
});

// ── Check 2: backfill ───────────────────────────────────────────────────

test('the backfill attributes old commission legs and makes the balance correct', function () {
    $party = fuUser('agent', 'backfill');
    $agent = Agent::factory()->create(['user_id' => $party->id]);

    $commission = Commission::create([
        'agent_id' => $agent->id, 'property_id' => fuProperty()->id,
        'property_price' => 100000, 'commission_rate' => 1.0, 'commission_amount' => 1000, 'status' => 'pending',
    ]);

    // An earning-only journal (a pre-payout balance).
    fuEarn($party, 1000, 'commission:'.$commission->id);

    // Simulate the pre-backfill state: the legs carry no party attribution.
    FinancialLedger::query()->where('transaction_group', 'commission:'.$commission->id)
        ->update(['party_user_id' => null]);

    $balances = app(AgentBalanceService::class);
    expect($balances->availableFor($party))->toBe(0.0);

    // Dry run reports but changes nothing.
    $this->artisan('ledger:backfill-party', ['--dry-run' => true])->assertSuccessful();
    expect($balances->availableFor($party))->toBe(0.0)
        ->and(FinancialLedger::where('transaction_group', 'commission:'.$commission->id)->whereNull('party_user_id')->count())->toBe(2);

    // The real run attributes the legs.
    $this->artisan('ledger:backfill-party')->assertSuccessful();

    expect(FinancialLedger::where('transaction_group', 'commission:'.$commission->id)->whereNull('party_user_id')->count())->toBe(0)
        ->and($balances->availableFor($party))->toBe(1000.0);

    // Idempotent: a second run has nothing left to do.
    $this->artisan('ledger:backfill-party')->expectsOutputToContain('nothing to backfill')->assertSuccessful();
});

// ── Check 3: hosts and professionals ────────────────────────────────────

test('hosts and professionals use the same balance and payout services', function () {
    $host = fuUser('host', 'same-services');
    $professional = fuUser('professional', 'same-services');

    fuEarn($host, 3000, 'payout:host-earn-1');
    fuEarn($professional, 4000, 'payout:pro-earn-1');

    $balances = app(AgentBalanceService::class);

    expect($balances->payoutableFor($host))->toBe(3000.0)
        ->and($balances->payoutableFor($professional))->toBe(4000.0);

    $hostPayout = app(PayoutService::class)->request($host, 2500, 'telebirr', null, null, 'host');
    $proPayout = app(PayoutService::class)->request($professional, 4000, 'bank_transfer', null, null, 'professional');

    expect($hostPayout->role)->toBe('host')
        ->and($proPayout->role)->toBe('professional')
        ->and((float) $hostPayout->gross_amount)->toBe(2500.0)
        ->and($balances->payoutableFor($host))->toBe(500.0);
});

test('a host can request a payout through the payout desk route', function () {
    $host = fuUser('host', 'route');
    fuEarn($host, 5000, 'payout:host-route');

    $this->actingAs($host)->post(route('finance.payouts.store'), [
        'gross_amount' => 3000, 'payment_method' => 'telebirr',
    ])->assertRedirect();

    expect(PayoutRequest::where('user_id', $host->id)->where('role', 'host')->exists())->toBeTrue();
});

test('a professional can request a payout through the payout desk route', function () {
    $professional = fuUser('professional', 'route');
    fuEarn($professional, 6000, 'payout:pro-route');

    $this->actingAs($professional)->post(route('finance.payouts.store'), [
        'gross_amount' => 6000, 'payment_method' => 'bank_transfer',
    ])->assertRedirect();

    expect(PayoutRequest::where('user_id', $professional->id)->exists())->toBeTrue();
});

test('a host can never approve or disburse a payout', function () {
    $host = fuUser('host', 'no-process');

    $payout = PayoutRequest::create([
        'payout_code' => 'PAY-REQ-HOST-NP', 'user_id' => $host->id, 'role' => 'host',
        'gross_amount' => 1000, 'withholding_tax' => 20, 'net_amount' => 980,
        'payment_method' => 'telebirr', 'status' => 'pending',
    ]);

    $this->actingAs($host)->patch(route('finance.payouts.process', $payout), ['decision' => 'approve'])
        ->assertForbidden();
});

test('a host reaches their own payout desk but not the platform books', function () {
    $this->seed(RolePermissionSeeder::class);

    $host = fuUser('host', 'own-desk');

    PayoutRequest::create([
        'payout_code' => 'PAY-HOST-DESK', 'user_id' => $host->id, 'role' => 'host',
        'gross_amount' => 1000, 'withholding_tax' => 20, 'net_amount' => 980,
        'payment_method' => 'telebirr', 'status' => 'pending',
    ]);

    $this->actingAs($host)->get(route('finance.index'))
        ->assertOk()
        ->assertSee('PAY-HOST-DESK')
        // The party's own desk never carries the finance disbursement control.
        ->assertDontSee('value="disburse"', false);
});

// ── Check 4: access control ─────────────────────────────────────────────

test('bank account numbers are encrypted at rest and masked on screen', function () {
    $finance = fuUser('finance_officer', 'encrypt');
    $party = fuUser('host', 'encrypt');

    $payout = PayoutRequest::create([
        'payout_code' => 'PAY-REQ-ENC-01', 'user_id' => $party->id, 'role' => 'host',
        'gross_amount' => 20000, 'withholding_tax' => 400, 'net_amount' => 19600,
        'payment_method' => 'bank_transfer', 'bank_name' => 'CBE', 'account_number' => '1000123456789',
        'status' => 'pending',
    ]);

    // At rest the column holds ciphertext, not the account number.
    $raw = DB::table('payout_requests')->where('id', $payout->id)->value('account_number');

    expect($raw)->not->toBe('1000123456789')
        ->and($raw)->toStartWith('eyJ')
        ->and($payout->fresh()->account_number)->toBe('1000123456789');

    // On screen it is masked to the last four digits.
    expect($payout->fresh()->masked_account_number)->toEndWith('6789')
        ->and($payout->fresh()->masked_account_number)->toContain('•');
});

test('a user sees only their own payouts and never the full bank details', function () {
    $agentUser = fuUser('agent', 'scope');
    $otherUser = fuUser('agent', 'scope-other');
    Agent::factory()->create(['user_id' => $agentUser->id]);
    Agent::factory()->create(['user_id' => $otherUser->id]);

    PayoutRequest::create([
        'payout_code' => 'PAY-MINE-01', 'user_id' => $agentUser->id, 'role' => 'agent',
        'gross_amount' => 1000, 'withholding_tax' => 20, 'net_amount' => 980,
        'payment_method' => 'bank_transfer', 'account_number' => '1000987654321', 'status' => 'pending',
    ]);
    PayoutRequest::create([
        'payout_code' => 'PAY-THEIRS-01', 'user_id' => $otherUser->id, 'role' => 'agent',
        'gross_amount' => 2000, 'withholding_tax' => 40, 'net_amount' => 1960,
        'payment_method' => 'bank_transfer', 'account_number' => '1000111122222', 'status' => 'pending',
    ]);

    $response = $this->actingAs($agentUser)->get(route('finance.index'));

    $response->assertOk()
        ->assertSee('PAY-MINE-01')
        ->assertDontSee('PAY-THEIRS-01')
        // The user's own account number is masked, never printed in full.
        ->assertDontSee('1000987654321');
});

test('only finance sees the full bank details', function () {
    $finance = fuUser('finance_officer', 'full-details');
    $party = fuUser('host', 'full-details');

    PayoutRequest::create([
        'payout_code' => 'PAY-FIN-01', 'user_id' => $party->id, 'role' => 'host',
        'gross_amount' => 1000, 'withholding_tax' => 20, 'net_amount' => 980,
        'payment_method' => 'bank_transfer', 'account_number' => '1000555566666', 'status' => 'pending',
    ]);

    $this->actingAs($finance)->get(route('finance.index'))
        ->assertOk()
        ->assertSee('1000555566666');
});

// ── Check 5: negative balances ──────────────────────────────────────────

test('future payouts net against a negative balance and finance sees who owes', function () {
    $finance = fuUser('finance_officer', 'negative');
    $party = fuUser('agent', 'negative');
    $agent = Agent::factory()->create(['user_id' => $party->id]);
    $property = fuProperty();

    $commission = Commission::create([
        'agent_id' => $agent->id, 'property_id' => $property->id,
        'property_price' => 100000, 'commission_rate' => 1.0, 'commission_amount' => 1000, 'status' => 'pending',
    ]);
    $commission->settlePayout($finance, 'bank_transfer');

    $payment = Payment::create([
        'property_id' => $property->id,
        'payer_name' => 'Buyer', 'payer_email' => 'buyer@example.com',
        'payment_type' => 'earnest_deposit', 'payment_method' => 'bank_transfer',
        'amount' => 100000, 'platform_fee' => 2000, 'tax_amount' => 300, 'status' => 'completed',
    ]);
    $this->actingAs($finance)->post(route('finance.payments.refund', $payment), [
        'refund_amount' => 100000, 'reason' => 'Deal reversed',
    ])->assertRedirect();

    $balances = app(AgentBalanceService::class);
    expect($balances->availableFor($party))->toBe(-980.0);

    // A new earning nets against the debt: -980 + 500 = -480.
    fuEarn($party, 500, 'commission:negative-topup');
    expect($balances->availableFor($party))->toBe(-480.0)
        ->and($balances->payoutableFor($party))->toBe(0.0);

    // Finance sees the list of people who owe the platform.
    $this->actingAs($finance)->get(route('finance.index'))
        ->assertOk()
        ->assertSee('owe the platform')
        ->assertSee($party->name);
});

// ── Check 6: reconciliation ─────────────────────────────────────────────

test('a bank reference that was already matched is rejected', function () {
    $finance = fuUser('finance_officer', 'recon-matched');

    Payment::create([
        'payer_name' => 'Recon', 'payer_email' => 'recon1@example.com',
        'payment_type' => 'earnest_deposit', 'payment_method' => 'bank_transfer',
        'bank_transaction_reference' => 'REF-MATCHED', 'amount' => 5000,
        'platform_fee' => 100, 'tax_amount' => 15, 'status' => 'completed',
    ]);

    $this->actingAs($finance)->post(route('finance.reconciliation.match'), ['reference' => 'REF-MATCHED'])
        ->assertStatus(409);
});

test('one transfer can settle several payments that share a reference', function () {
    $finance = fuUser('finance_officer', 'recon-multi');

    foreach ([3000, 2000] as $amount) {
        Payment::create([
            'payer_name' => 'Recon', 'payer_email' => 'recon'.uniqid().'@example.com',
            'payment_type' => 'earnest_deposit', 'payment_method' => 'bank_transfer',
            'bank_transaction_reference' => 'REF-BATCH', 'amount' => $amount,
            'platform_fee' => round($amount * 0.02, 2), 'tax_amount' => round($amount * 0.02 * 0.15, 2),
            'status' => 'pending',
        ]);
    }

    $this->actingAs($finance)->post(route('finance.reconciliation.match'), [
        'reference' => 'REF-BATCH', 'amount' => 5000,
    ])->assertRedirect();

    expect(Payment::where('bank_transaction_reference', 'REF-BATCH')->where('status', 'completed')->count())->toBe(2)
        ->and(FinancialLedger::where('transaction_group', 'like', 'payment:%')->count())->toBeGreaterThan(0);
});

test('an amount mismatch is flagged and not auto-matched', function () {
    $finance = fuUser('finance_officer', 'recon-mismatch');

    $payment = Payment::create([
        'payer_name' => 'Recon', 'payer_email' => 'recon-mismatch@example.com',
        'payment_type' => 'earnest_deposit', 'payment_method' => 'bank_transfer',
        'bank_transaction_reference' => 'REF-SHORT', 'amount' => 5000,
        'platform_fee' => 100, 'tax_amount' => 15, 'status' => 'pending',
    ]);

    $this->actingAs($finance)->post(route('finance.reconciliation.match'), [
        'reference' => 'REF-SHORT', 'amount' => 4000,
    ])->assertRedirect();

    expect($payment->fresh()->status)->toBe('pending')
        ->and(FinanceReviewFlag::where('payment_id', $payment->id)->count())->toBe(1);
});
