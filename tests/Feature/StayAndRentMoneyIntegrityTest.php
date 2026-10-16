<?php

use App\Models\Booking;
use App\Models\FinancialLedger;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\RentSchedule;
use App\Models\UnitReservation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * P1 money correctness: the landlord rent desk and the stay-cancellation refund.
 *
 * Both now write a real money record and a balanced journal through the same
 * writers the rest of the platform uses, a failed or refunded payment rolls its
 * payable back, and both payment and refund creation are idempotent.
 */
function batchBFinance(): User
{
    return User::create([
        'name' => 'Batch B Finance',
        'email' => 'batchb.finance.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'finance_officer',
    ]);
}

/**
 * @return array{0: float, 1: float}
 */
function batchBGroupTotals(string $group): array
{
    $legs = FinancialLedger::where('transaction_group', $group)->get();

    return [
        round((float) $legs->where('entry_type', 'debit')->sum('amount'), 2),
        round((float) $legs->where('entry_type', 'credit')->sum('amount'), 2),
    ];
}

test('landlord rent recording writes a verified payment and a balanced journal', function () {
    $landlord = User::factory()->create(['role' => 'landlord']);
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create(['user_id' => $landlord->id]);

    $lease = Lease::create([
        'property_id' => $property->id,
        'landlord_user_id' => $landlord->id,
        'tenant_user_id' => $tenant->id,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'tenant_phone' => '+251911000000',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(12)->toDateString(),
        'duration_months' => 12,
        'monthly_rent' => 30000,
        'status' => 'active',
    ]);

    $schedule = RentSchedule::create([
        'lease_id' => $lease->id,
        'installment_number' => 1,
        'period_label' => 'January 2026',
        'due_date' => now()->toDateString(),
        'amount_due' => 30000,
        'amount_paid' => 0,
        'status' => 'pending',
    ]);

    $this->actingAs($landlord)->post(route('seller.rent.record', $schedule), [
        'amount_paid' => 30000,
        'payment_method' => 'telebirr',
        'notes' => 'Received via Telebirr merchant payment',
    ])->assertRedirect(route('seller.dashboard'));

    $payment = Payment::where('payable_type', RentSchedule::class)
        ->where('payable_id', $schedule->id)
        ->latest('id')
        ->first();

    expect($payment)->not->toBeNull()
        ->and($payment->payment_type)->toBe('rent_installment')
        ->and($payment->status)->toBe('completed')
        ->and((float) $payment->amount)->toBe(30000.0)
        ->and((float) $payment->platform_fee)->toBe(0.0);

    $this->assertDatabaseHas('financial_ledgers', [
        'transaction_group' => 'payment:'.$payment->id,
        'category' => 'cash',
        'entry_type' => 'debit',
        'amount' => 30000.00,
    ]);

    [$debits, $credits] = batchBGroupTotals('payment:'.$payment->id);

    expect($debits)->toBe(30000.0)
        ->and($credits)->toBe(30000.0)
        ->and($schedule->fresh()->status)->toBe('paid');
});

test('a stay cancellation separates the guest service fee from the platform fee', function () {
    $guest = User::factory()->create(['role' => 'traveler']);
    $property = Property::factory()->create(['type' => 'stay']);

    $booking = Booking::create([
        'booking_reference' => 'STAY-BB-REFUND-1',
        'property_id' => $property->id,
        'user_id' => $guest->id,
        'guest_name' => $guest->name,
        'guest_email' => $guest->email,
        'guest_phone' => '+251911000000',
        'check_in_date' => now()->addDays(5)->toDateString(),
        'check_out_date' => now()->addDays(8)->toDateString(),
        'total_nights' => 3,
        'nightly_price' => 2000,
        'accommodation_total' => 6000,
        'cleaning_fee' => 500,
        // The guest-facing service fee is the policy's own retention (300).
        'service_fee' => 300,
        'total_amount' => 6800,
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ]);

    $payment = Payment::create([
        'payable_type' => Booking::class,
        'payable_id' => $booking->id,
        'property_id' => $property->id,
        'user_id' => $guest->id,
        'payer_name' => $guest->name,
        'payer_email' => $guest->email,
        'payment_type' => 'stay_booking',
        'payment_method' => 'telebirr',
        'amount' => 6800,
        // The platform fee is a different, smaller number (2% of 6,800).
        'platform_fee' => 136,
        'tax_amount' => 20.4,
        'status' => 'completed',
    ]);

    $this->actingAs($guest)->post(route('guest.bookings.cancel', $booking), [
        'cancellation_reason' => 'Trip postponed',
    ])->assertRedirect();

    // The policy keeps the 300 guest-facing service fee, so 6,500 goes back.
    expect($booking->fresh()->status)->toBe('cancelled')
        ->and((float) $booking->fresh()->refund_amount)->toBe(6500.0)
        ->and((float) $payment->fresh()->refunded_amount)->toBe(6500.0);

    // The platform fee (136) and its VAT (20.4) are reversed proportionally:
    // 136 * 6500/6800 = 130 and 20.4 * 6500/6800 = 19.5.
    $refundLegs = FinancialLedger::where('payment_id', $payment->id)
        ->where('category', 'refund_adjustment')
        ->get();

    expect($refundLegs)->toHaveCount(1)
        ->and((float) $refundLegs->first()->amount)->toBe(6350.5)
        ->and((float) FinancialLedger::where('payment_id', $payment->id)->where('category', 'cash')->where('entry_type', 'credit')->sum('amount'))->toBe(6500.0)
        ->and((float) FinancialLedger::where('payment_id', $payment->id)->where('category', 'platform_fee')->where('entry_type', 'debit')->sum('amount'))->toBe(130.0)
        ->and((float) FinancialLedger::where('payment_id', $payment->id)->where('category', 'vat_payable')->where('entry_type', 'debit')->sum('amount'))->toBe(19.5);

    // The refund is documented by a credit note because it reversed VAT.
    expect($refundLegs->first()->payment->refunds()->first()->credit_note_number)->not->toBeNull();

    [$debits, $credits] = batchBGroupTotals($refundLegs->first()->transaction_group);

    expect($debits)->toBe(6500.0)
        ->and($credits)->toBe(6500.0);
});

test('refunding a payment rolls its offer, reservation and rent schedule back', function () {
    $finance = batchBFinance();

    // Offer: a full refund rejects it.
    $offer = PropertyOffer::factory()->create(['status' => 'under_review']);
    $offerPayment = Payment::create([
        'payable_type' => PropertyOffer::class,
        'payable_id' => $offer->id,
        'payer_name' => 'Offer Buyer',
        'payer_email' => 'offer.buyer@example.com',
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'bank_transfer',
        'amount' => 25000,
        'platform_fee' => 500,
        'tax_amount' => 75,
        'status' => 'completed',
    ]);

    $this->actingAs($finance)->post(route('finance.payments.refund', $offerPayment), [
        'refund_amount' => 25000,
        'reason' => 'Deal fell through',
    ])->assertRedirect();

    expect($offer->fresh()->status)->toBe('rejected');

    // Reservation: a full refund cancels it.
    $reservation = UnitReservation::factory()->create([
        'status' => 'reserved',
        'deposit_amount' => 100000,
    ]);
    $resPayment = Payment::create([
        'payable_type' => UnitReservation::class,
        'payable_id' => $reservation->id,
        'payer_name' => 'Res Buyer',
        'payer_email' => 'res.buyer@example.com',
        'payment_type' => 'unit_reservation',
        'payment_method' => 'bank_transfer',
        'amount' => 100000,
        'platform_fee' => 2000,
        'tax_amount' => 300,
        'status' => 'completed',
    ]);

    $this->actingAs($finance)->post(route('finance.payments.refund', $resPayment), [
        'refund_amount' => 100000,
        'reason' => 'Buyer withdrew',
    ])->assertRedirect();

    expect($reservation->fresh()->status)->toBe('cancelled');

    // Rent: a full refund returns the schedule to unpaid.
    $lease = Lease::create([
        'property_id' => Property::factory()->create()->id,
        'tenant_name' => 'Tenant',
        'tenant_email' => 'tenant@example.com',
        'tenant_phone' => '+251911000333',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(12)->toDateString(),
        'duration_months' => 12,
        'monthly_rent' => 30000,
        'status' => 'active',
    ]);
    $schedule = RentSchedule::create([
        'lease_id' => $lease->id,
        'installment_number' => 1,
        'period_label' => 'February 2026',
        'due_date' => now()->toDateString(),
        'amount_due' => 30000,
        'amount_paid' => 30000,
        'status' => 'paid',
    ]);
    $rentPayment = Payment::create([
        'payable_type' => RentSchedule::class,
        'payable_id' => $schedule->id,
        'payer_name' => 'Tenant',
        'payer_email' => 'tenant@example.com',
        'payment_type' => 'rent_installment',
        'payment_method' => 'cash',
        'amount' => 30000,
        'platform_fee' => 0,
        'tax_amount' => 0,
        'status' => 'completed',
    ]);

    $this->actingAs($finance)->post(route('finance.payments.refund', $rentPayment), [
        'refund_amount' => 30000,
        'reason' => 'Collected in error',
    ])->assertRedirect();

    expect($schedule->fresh()->status)->toBe('pending')
        ->and((float) $schedule->fresh()->amount_paid)->toBe(0.0);
});

test('rejecting a payment reopens the offer it was paying for', function () {
    $finance = batchBFinance();

    $offer = PropertyOffer::factory()->create(['status' => 'under_review']);

    $payment = Payment::create([
        'payable_type' => PropertyOffer::class,
        'payable_id' => $offer->id,
        'user_id' => User::factory()->create()->id,
        'payer_name' => 'Retry Buyer',
        'payer_email' => 'retry.buyer@example.com',
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'bank_transfer',
        'amount' => 20000,
        'platform_fee' => 400,
        'tax_amount' => 60,
        'status' => 'pending',
    ]);

    $this->actingAs($finance)->put(route('payments.reject', $payment), [
        'admin_notes' => 'Deposit slip unreadable',
    ])->assertRedirect();

    expect($payment->fresh()->status)->toBe('failed')
        ->and($offer->fresh()->status)->toBe('submitted');
});

test('a repeated payment submission with the same idempotency key creates one payment', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $payload = [
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'telebirr',
        'amount' => 15000,
        'payer_name' => $buyer->name,
        'payer_email' => $buyer->email,
    ];

    $this->actingAs($buyer)->withHeader('Idempotency-Key', 'pay-once-1')->post(route('payments.store'), $payload)->assertRedirect();
    $this->actingAs($buyer)->withHeader('Idempotency-Key', 'pay-once-1')->post(route('payments.store'), $payload)->assertRedirect();

    expect(Payment::where('idempotency_key', 'pay-once-1')->count())->toBe(1)
        ->and(Payment::count())->toBe(1);
});

test('a repeated refund with the same idempotency key refunds once', function () {
    $finance = batchBFinance();

    $payment = Payment::create([
        'payer_name' => 'Idem Payer',
        'payer_email' => 'idem.payer@example.com',
        'payment_type' => 'earnest_deposit',
        'payment_method' => 'bank_transfer',
        'amount' => 1000,
        'platform_fee' => 20,
        'tax_amount' => 3,
        'status' => 'completed',
    ]);

    $payload = ['refund_amount' => 400, 'reason' => 'Duplicate retry'];

    $this->actingAs($finance)->withHeader('Idempotency-Key', 'refund-once-1')->post(route('finance.payments.refund', $payment), $payload)->assertRedirect();
    $this->actingAs($finance)->withHeader('Idempotency-Key', 'refund-once-1')->post(route('finance.payments.refund', $payment), $payload)->assertRedirect();

    expect((float) $payment->fresh()->refunded_amount)->toBe(400.0)
        ->and(FinancialLedger::where('payment_id', $payment->id)->where('category', 'refund_adjustment')->count())->toBe(1);
});
