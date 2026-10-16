<?php

use App\Models\FinancialLedger;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * P1 money correctness: the legacy/current ledger split.
 *
 * Rows written before the double-entry change are single-sided and are marked
 * `is_legacy`. They are never rewritten, but every finance report must handle
 * both kinds: the balance audit only covers balanced journals, while legacy
 * single-sided rows are shown as their own totals and are kept out of net
 * revenue and the profit and loss.
 */
function legacyAdmin(): User
{
    return User::create([
        'name' => 'Legacy Ledger Admin',
        'email' => 'legacy.admin.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);
}

test('new journals are current, not legacy', function () {
    $buyer = User::create([
        'name' => 'Legacy Buyer',
        'email' => 'legacy.buyer.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->actingAs($buyer)->post(route('payments.store'), [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => 10000,
        'payer_name' => 'Legacy Buyer',
        'payer_email' => $buyer->email,
    ])->assertRedirect();

    $payment = Payment::latest('id')->first();
    $this->actingAs(legacyAdmin())->put(route('payments.verify', $payment), [])->assertRedirect();

    $legs = FinancialLedger::where('transaction_group', 'payment:'.$payment->id)->get();

    expect($legs)->not->toBeEmpty()
        ->and($legs->every(fn ($leg) => $leg->is_legacy === false))->toBeTrue();
});

test('a legacy single-sided row is excluded from the balance audit', function () {
    // A pre-change credit-only row: no `transaction_group`, flagged legacy.
    $legacy = FinancialLedger::create([
        'entry_type' => 'credit',
        'category' => 'platform_fee',
        'amount' => 2000,
        'currency' => 'ETB',
        'description' => 'Pre-change platform fee (single-sided)',
        'is_legacy' => true,
    ]);

    expect($legacy->is_legacy)->toBeTrue()
        ->and($legacy->transaction_group)->toBeNull();

    // The audit groups by `transaction_group`, so a legacy row can never make
    // the books look unbalanced.
    $unbalanced = FinancialLedger::query()
        ->whereNotNull('transaction_group')
        ->get()
        ->groupBy('transaction_group')
        ->filter(fn ($legs) => round((float) $legs->where('entry_type', 'debit')->sum('amount'), 2)
            !== round((float) $legs->where('entry_type', 'credit')->sum('amount'), 2));

    expect($unbalanced->keys()->all())->toBe([]);
});

test('the finance report keeps legacy rows out of net revenue and shows them separately', function () {
    FinancialLedger::create([
        'entry_type' => 'credit',
        'category' => 'platform_fee',
        'amount' => 2000,
        'currency' => 'ETB',
        'description' => 'Pre-change platform fee',
        'is_legacy' => true,
    ]);

    $response = $this->actingAs(legacyAdmin())->get(route('finance.index'));

    $response->assertOk()
        ->assertSee('legacy', false)
        ->assertViewHas('stats', function (array $stats): bool {
            // The legacy credit is surfaced on its own and is never folded into
            // the platform-fee revenue or the profit and loss.
            return $stats['legacy_ledger_entries'] >= 1
                && $stats['legacy_inflow'] >= 2000.0
                && $stats['platform_fees'] === 0.0
                && $stats['gross_inflow'] === 0.0;
        });
});

test('the ledger export flags legacy and current rows', function () {
    FinancialLedger::create([
        'entry_type' => 'credit',
        'category' => 'platform_fee',
        'amount' => 2000,
        'currency' => 'ETB',
        'description' => 'Pre-change platform fee',
        'is_legacy' => true,
    ]);

    $response = $this->actingAs(legacyAdmin())->get(route('finance.export-csv'));

    $response->assertOk();
    expect($response->streamedContent())->toContain('Legacy (single-sided)');
});
