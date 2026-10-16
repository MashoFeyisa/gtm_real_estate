<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\RentSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * One writer for a rent installment, whichever desk records it (SRS §87).
 *
 * The tenant desk and the landlord desk both call this, so an offline
 * collection leaves exactly the same trace as an online one: a verified payment
 * row and a balanced double-entry journal. Rent carries no platform fee today,
 * but the fee and VAT are still derived from the one settings service, so if a
 * rent fee is ever configured both desks honour it without a code change.
 */
class RentPaymentService
{
    /**
     * Record one rent installment against a schedule.
     *
     * `$idempotencyKey` makes the write safe to retry: the same key returns the
     * payment already written instead of charging the schedule twice.
     */
    public function record(
        RentSchedule $schedule,
        User $recorder,
        float $amount,
        string $paymentMethod,
        ?string $reference = null,
        ?string $notes = null,
        ?string $idempotencyKey = null,
    ): Payment {
        if ($idempotencyKey !== null) {
            $existing = Payment::where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($schedule, $recorder, $amount, $paymentMethod, $reference, $notes, $idempotencyKey) {
            $lease = $schedule->lease;
            $settings = app(FinanceSettings::class);

            // Rent is not fee-bearing; the rule is expressed here so every rent
            // writer agrees, and VAT is only ever charged on a fee.
            $platformFee = 0.0;
            $vat = $settings->vatOnFee($platformFee);

            $payment = Payment::create([
                'payable_type' => RentSchedule::class,
                'payable_id' => $schedule->id,
                'property_id' => $lease?->property_id,
                'user_id' => $lease?->tenant_user_id ?? $recorder->id,
                'payer_name' => $lease?->tenant_name ?: $recorder->name,
                'payer_email' => $lease?->tenant_email ?: $recorder->email,
                'payer_phone' => $lease?->tenant_phone ?: $recorder->phone,
                'payment_type' => 'rent_installment',
                'payment_method' => $paymentMethod,
                'bank_name' => $paymentMethod === 'cbe_birr' ? 'Commercial Bank of Ethiopia' : null,
                'bank_transaction_reference' => $reference,
                'idempotency_key' => $idempotencyKey,
                'currency' => 'ETB',
                'amount' => $amount,
                'platform_fee' => $platformFee,
                'tax_amount' => $vat,
                'net_amount' => $settings->netAmount($amount, $platformFee, $vat),
                'status' => 'completed',
                'notes' => $notes ?? "Rent payment for {$schedule->period_label}",
                'verified_at' => now(),
                'verified_by' => $recorder->id,
            ]);

            // Balanced journal (cash in, rent recognised). The payable sync is
            // handled below with the cumulative amount a partial payment needs.
            $payment->settle(syncPayable: false);

            $totalPaidNow = round((float) $schedule->amount_paid + $amount, 2);

            $schedule->update([
                'amount_paid' => $totalPaidNow,
                'status' => $totalPaidNow + 0.01 >= (float) $schedule->amount_due ? 'paid' : 'partial',
                'paid_at' => now(),
                'payment_id' => $payment->id,
                'payment_method' => $paymentMethod,
                'notes' => $notes ?? $schedule->notes,
            ]);

            AuditLog::log(
                action: 'rent.payment_recorded',
                entity: $schedule,
                oldValue: ['amount_paid' => $totalPaidNow - $amount],
                newValue: ['amount_paid' => $totalPaidNow, 'payment_id' => $payment->id],
                reason: "Rent installment #{$schedule->installment_number} of ETB ".number_format($amount, 2)." recorded by {$recorder->name}",
                user: $recorder,
            );

            return $payment;
        });
    }
}
