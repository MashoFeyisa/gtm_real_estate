<?php

use App\Models\Booking;
use App\Models\FinancialLedger;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * P0 money correctness: refunds (SRS §132).
 *
 * A payment can only be refunded up to its original amount minus what has
 * already been refunded, the linked payable is reversed or flagged, and every
 * refund writes an auditable ledger debit.
 */
function refundFinance(): User
{
    return User::create([
        'name' => 'Finance Refund Officer',
        'email' => 'refund.officer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'finance_officer',
    ]);
}

function refundablePayment(float $amount, array $attributes = []): Payment
{
    $payer = User::create([
        'name' => 'Refund Payer',
        'email' => 'refund.payer.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    return Payment::create(array_merge([
        'user_id' => $payer->id,
        'payer_name' => $payer->name,
        'payer_email' => $payer->email,
        'payment_type' => 'stay_booking',
        'payment_method' => 'bank_transfer',
        'amount' => $amount,
        'platform_fee' => round($amount * 0.02, 2),
        'tax_amount' => 0,
        'net_amount' => round($amount * 0.98, 2),
        'status' => 'completed',
        'verified_at' => now(),
    ], $attributes));
}

test('a refund may not exceed the original amount minus what was already refunded', function () {
    $finance = refundFinance();
    $payment = refundablePayment(1000);

    $this->actingAs($finance)
        ->postJson(route('finance.payments.refund', $payment), ['refund_amount' => 600, 'reason' => 'first'])
        ->assertRedirect();

    expect($payment->fresh()->refunded_amount)->toEqual(600.0)
        ->and($payment->fresh()->status)->toBe('partially_refunded');

    // 600 + 600 > 1000, so the second attempt must be refused and change nothing.
    $this->actingAs($finance)
        ->postJson(route('finance.payments.refund', $payment), ['refund_amount' => 600, 'reason' => 'second'])
        ->assertStatus(422);

    expect($payment->fresh()->refunded_amount)->toEqual(600.0);

    // The remaining 400 is allowed and settles the payment.
    $this->actingAs($finance)
        ->postJson(route('finance.payments.refund', $payment), ['refund_amount' => 400, 'reason' => 'remainder'])
        ->assertRedirect();

    expect($payment->fresh()->refunded_amount)->toEqual(1000.0)
        ->and($payment->fresh()->status)->toBe('refunded');
});

test('a fully refunded payment cannot be refunded again', function () {
    $finance = refundFinance();
    $payment = refundablePayment(500, ['refunded_amount' => 500, 'status' => 'refunded']);

    $this->actingAs($finance)
        ->postJson(route('finance.payments.refund', $payment), ['refund_amount' => 10, 'reason' => 'again'])
        ->assertStatus(422);

    expect($payment->fresh()->refunded_amount)->toEqual(500.0);
});

test('every refund writes an audit debit to the ledger', function () {
    $finance = refundFinance();
    $payment = refundablePayment(1000);

    $this->actingAs($finance)
        ->postJson(route('finance.payments.refund', $payment), ['refund_amount' => 250, 'reason' => 'adjustment'])
        ->assertRedirect();

    $debits = FinancialLedger::where('payment_id', $payment->id)
        ->where('category', 'refund_adjustment')
        ->where('entry_type', 'debit')
        ->get();

    // 250 refunded out of 1,000 with a 20 fee reverses 5 of the fee (a quarter),
    // so the beneficiary's revenue reversal is 245 and the journal still balances.
    expect($debits)->toHaveCount(1)
        ->and((float) $debits->first()->amount)->toEqual(245.0);

    expect((float) FinancialLedger::where('payment_id', $payment->id)
        ->where('category', 'platform_fee')->where('entry_type', 'debit')->sum('amount'))->toEqual(5.0);
});

function refundableBooking(array $attributes = []): Booking
{
    $guest = User::create([
        'name' => 'Refund Guest',
        'email' => 'refund.guest.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'traveler',
    ]);

    $property = Property::create([
        'property_code' => 'ETH-AA-REFUND-'.strtoupper(Str::random(6)),
        'title' => 'Refund Test Stay',
        'slug' => 'refund-test-stay-'.Str::random(8),
        'type' => 'short_stay',
        'property_category' => 'apartment',
        'price' => 1000,
        'city' => 'Addis Ababa',
        'is_active' => true,
        'status' => 'published',
    ]);

    return Booking::create(array_merge([
        'property_id' => $property->id,
        'user_id' => $guest->id,
        'guest_name' => 'Refund Guest',
        'guest_email' => 'refund.guest@example.com',
        'guest_phone' => '+251911000000',
        'check_in_date' => now()->addDays(5)->toDateString(),
        'check_out_date' => now()->addDays(7)->toDateString(),
        'total_nights' => 2,
        'guests_count' => 2,
        'nightly_price' => 500,
        'accommodation_total' => 1000,
        'total_amount' => 1000,
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ], $attributes));
}

test('a refund reverses the linked booking and flags a partial refund', function () {
    $finance = refundFinance();
    $booking = refundableBooking();

    $payment = refundablePayment(1000, [
        'payable_type' => Booking::class,
        'payable_id' => $booking->id,
    ]);

    $this->actingAs($finance)
        ->postJson(route('finance.payments.refund', $payment), ['refund_amount' => 300, 'reason' => 'partial cancellation'])
        ->assertRedirect();

    $booking->refresh();
    expect($booking->status)->toBe('cancelled')
        ->and($booking->payment_status)->toBe('refunded')
        ->and((float) $booking->refund_amount)->toEqual(300.0)
        ->and($booking->refund_status)->toBe('pending_disbursement');
});

test('a full refund marks the booking fully refunded', function () {
    $finance = refundFinance();
    $booking = refundableBooking();

    $payment = refundablePayment(1000, [
        'payable_type' => Booking::class,
        'payable_id' => $booking->id,
    ]);

    $this->actingAs($finance)
        ->postJson(route('finance.payments.refund', $payment), ['refund_amount' => 1000, 'reason' => 'full cancellation'])
        ->assertRedirect();

    $booking->refresh();
    expect($booking->refund_status)->toBe('refunded')
        ->and((float) $booking->refund_amount)->toEqual(1000.0);
});
