<?php

use App\Models\FinanceRate;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * P1 money correctness: the dated finance-rate book (SRS §171).
 *
 * A rate change is a new fact, not an edit. It needs the
 * `finance.settings.update` permission, a written reason and an audit entry,
 * and it only applies from its effective date forward - records already written
 * keep the rate they snapshotted.
 */
function rateOfficer(string $role, string $email): User
{
    return User::create([
        'name' => ucfirst($role).' Rate Officer',
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => $role,
    ]);
}

test('a caller without finance.settings.update cannot change a rate', function () {
    $agent = rateOfficer('agent', 'rate.agent@example.com');

    $this->actingAs($agent)
        ->post(route('finance.settings.rates.store'), [
            'key' => 'platform_fee',
            'rate' => 9,
            'effective_from' => now()->toDateString(),
            'reason' => 'Should be refused',
        ])
        ->assertForbidden();

    expect(FinanceRate::count())->toBe(0);
});

test('a finance officer records a dated rate and it is audit-logged', function () {
    $finance = rateOfficer('finance_officer', 'rate.finance@example.com');

    $this->actingAs($finance)
        ->post(route('finance.settings.rates.store'), [
            'key' => 'platform_fee',
            'rate' => 2.5,
            'effective_from' => now()->toDateString(),
            'reason' => 'Board decision 2026-10',
        ])
        ->assertRedirect();

    $rate = FinanceRate::latest('id')->first();

    expect($rate)->not->toBeNull()
        ->and($rate->key)->toBe('platform_fee')
        ->and((float) $rate->rate)->toBe(2.5)
        ->and($rate->created_by)->toBe($finance->id)
        ->and($rate->reason)->toBe('Board decision 2026-10');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'finance.rate_updated',
        'user_id' => $finance->id,
    ]);
});

test('a rate change without a reason is refused', function () {
    $finance = rateOfficer('finance_officer', 'rate.noreason@example.com');

    $this->actingAs($finance)
        ->post(route('finance.settings.rates.store'), [
            'key' => 'vat',
            'rate' => 15,
            'effective_from' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('reason');

    expect(FinanceRate::count())->toBe(0);
});

test('a rate change applies to future transactions only', function () {
    // A payment written under the old rate snapshots it.
    $buyer = rateOfficer('buyer', 'rate.buyer@example.com');
    $this->actingAs($buyer)->post(route('payments.store'), [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => 100000,
        'payer_name' => 'Rate Buyer',
        'payer_email' => 'rate.buyer@example.com',
    ])->assertRedirect();

    $oldPayment = Payment::latest('id')->first();
    expect((float) $oldPayment->platform_fee_rate)->toBe(2.0);

    // The finance officer doubles the fee effective today.
    $finance = rateOfficer('finance_officer', 'rate.future@example.com');
    $this->actingAs($finance)->post(route('finance.settings.rates.store'), [
        'key' => 'platform_fee',
        'rate' => 4,
        'effective_from' => now()->toDateString(),
        'reason' => 'New fee schedule',
    ])->assertRedirect();

    // The old record is untouched; a new transaction uses the new rate.
    $oldPayment->refresh();
    expect((float) $oldPayment->platform_fee_rate)->toBe(2.0)
        ->and((float) $oldPayment->platform_fee)->toBe(2000.0);

    $this->actingAs($buyer)->post(route('payments.store'), [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => 100000,
        'payer_name' => 'Rate Buyer',
        'payer_email' => 'rate.buyer@example.com',
    ])->assertRedirect();

    $newPayment = Payment::latest('id')->first();
    expect((float) $newPayment->platform_fee_rate)->toBe(4.0)
        ->and((float) $newPayment->platform_fee)->toBe(4000.0);
});

test('a backdated effective date is refused', function () {
    $finance = rateOfficer('finance_officer', 'rate.backdate@example.com');

    $this->actingAs($finance)
        ->post(route('finance.settings.rates.store'), [
            'key' => 'vat',
            'rate' => 20,
            'effective_from' => now()->subDay()->toDateString(),
            'reason' => 'Too late',
        ])
        ->assertSessionHasErrors('effective_from');
});
