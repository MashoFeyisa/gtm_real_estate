<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * One writer for a refund, whichever desk issues it (SRS §132, §170).
 *
 * The finance desk and a guest cancelling a stay both call this, so a refund
 * always leaves the same trace: a `refunds` fact row, the payment's refunded
 * total advanced under a row lock, the platform fee and the VAT on it reversed,
 * a credit note for any VAT reversal, and a balanced double-entry journal. The
 * reversal is measured cumulatively so several partial refunds always sum to
 * the whole.
 *
 * The reversal is idempotent per key: replaying the same key with the same
 * payload returns the original refund; the same key with a different payload is
 * a conflict (HTTP 409).
 */
class RefundService
{
    public function __construct(private LedgerService $ledger) {}

    /**
     * Reverse part or all of a payment.
     *
     * @param  bool  $reverseFee  whether to reverse the platform fee and VAT in
     *                            proportion to the refund. A policy that keeps
     *                            the fee (e.g. a stay cancellation) passes false,
     *                            and the whole refund comes out of the revenue.
     * @return float the amount refunded (the original amount when the key is replayed)
     */
    public function refund(
        Payment $payment,
        float $amount,
        string $reason,
        User $actor,
        ?string $idempotencyKey = null,
        bool $reverseFee = true,
    ): float {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            return 0.0;
        }

        $fingerprint = $this->fingerprint($payment, $amount, $reason, $reverseFee);

        if ($idempotencyKey !== null) {
            $replay = $this->resolveReplay($idempotencyKey, $fingerprint);

            if ($replay !== null) {
                return $replay;
            }
        }

        $group = $idempotencyKey !== null
            ? 'refund:'.$idempotencyKey
            : 'LED-ADJ-REF-'.strtoupper(substr(uniqid(), -8));

        if ($idempotencyKey !== null && $this->ledger->hasPosted($group)) {
            return (float) Refund::where('idempotency_key', $idempotencyKey)->value('amount');
        }

        $refunded = 0.0;

        DB::transaction(function () use ($payment, $amount, $reason, $actor, $group, $idempotencyKey, $reverseFee, $fingerprint, &$refunded) {
            // Re-read under lock: a second concurrent request must not be able
            // to push the refunded total past the original amount.
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $remaining = $this->refundableAmount($locked);

            abort_if($amount - $remaining > 0.004, 422, 'Refund amount exceeds the remaining refundable balance.');

            if ($idempotencyKey !== null && $this->ledger->hasPosted($group)) {
                $refunded = (float) Refund::where('idempotency_key', $idempotencyKey)->value('amount');

                return;
            }

            $isFullRefund = $amount >= ($remaining - 0.004);
            $previousRefunded = (float) $locked->refunded_amount;
            $cumulativeRefunded = round($previousRefunded + $amount, 2);

            $locked->update([
                'status' => $isFullRefund ? 'refunded' : 'partially_refunded',
                'refunded_amount' => $cumulativeRefunded,
                'refund_reason' => $reason,
            ]);

            $total = (float) $locked->amount;
            $feeReversal = $reverseFee
                ? $this->proportionalReversal((float) $locked->platform_fee, $total, $previousRefunded, $cumulativeRefunded)
                : 0.0;
            $vatReversal = $reverseFee
                ? $this->proportionalReversal((float) $locked->tax_amount, $total, $previousRefunded, $cumulativeRefunded)
                : 0.0;
            $beneficiaryReversal = round($amount - $feeReversal - $vatReversal, 2);

            // Add immutable double-entry adjustment: the refunded cash leaves
            // the business and the revenue, fee and VAT it recognised are
            // reversed. Debits (beneficiary + fee + VAT) equal the cash credit.
            $legs = [
                [
                    'payment_id' => $locked->id,
                    'entry_type' => 'debit',
                    'category' => 'refund_adjustment',
                    'amount' => $beneficiaryReversal,
                    'currency' => $locked->currency ?? 'ETB',
                    'description' => "Refund adjustment for Payment #{$locked->transaction_number}: {$reason}",
                    'reference' => $locked->transaction_number,
                    'user_id' => $actor->id,
                ],
                [
                    'payment_id' => $locked->id,
                    'entry_type' => 'credit',
                    'category' => 'cash',
                    'amount' => $amount,
                    'currency' => $locked->currency ?? 'ETB',
                    'description' => "Cash refunded for Payment #{$locked->transaction_number}: {$reason}",
                    'reference' => $locked->transaction_number,
                    'user_id' => $actor->id,
                ],
            ];

            if ($feeReversal != 0.0) {
                $legs[] = [
                    'payment_id' => $locked->id,
                    'entry_type' => 'debit',
                    'category' => 'platform_fee',
                    'amount' => $feeReversal,
                    'currency' => $locked->currency ?? 'ETB',
                    'description' => "Platform fee reversed for Payment #{$locked->transaction_number}: {$reason}",
                    'reference' => $locked->transaction_number,
                    'user_id' => $actor->id,
                ];
            }

            if ($vatReversal != 0.0) {
                $legs[] = [
                    'payment_id' => $locked->id,
                    'entry_type' => 'debit',
                    'category' => 'vat_payable',
                    'amount' => $vatReversal,
                    'currency' => $locked->currency ?? 'ETB',
                    'description' => "VAT on the platform fee reversed for Payment #{$locked->transaction_number}: {$reason}",
                    'reference' => $locked->transaction_number,
                    'user_id' => $actor->id,
                ];
            }

            $this->ledger->post($group, $legs);

            // SRS §170: a refund that reverses VAT is documented by a credit note.
            $refund = Refund::create([
                'payment_id' => $locked->id,
                'amount' => $amount,
                'fee_reversed' => $feeReversal,
                'vat_reversed' => $vatReversal,
                'reason' => $reason,
                'ledger_group' => $group,
                'credit_note_number' => $vatReversal > 0 ? $this->creditNoteNumber() : null,
                'idempotency_key' => $idempotencyKey,
                'idempotency_hash' => $idempotencyKey !== null ? $fingerprint : null,
                'created_by' => $actor->id,
            ]);

            // Reverse or flag whatever this payment had advanced (rollback safety).
            $locked->reversePayableAfterRefund($isFullRefund);

            // A refund after the commission was already paid out is a recovery:
            // reverse the party's earning so their ledger-derived balance goes
            // negative (SRS §132, §194).
            $this->recoverCommissions($locked, $total, $previousRefunded, $cumulativeRefunded, $group, $actor);

            AuditLog::log(
                user: $actor,
                action: 'payment.refund_issued',
                entity: $locked,
                oldValue: ['status' => $locked->getOriginal('status'), 'refunded_amount' => $locked->getOriginal('refunded_amount')],
                newValue: [
                    'status' => $locked->status,
                    'refunded_amount' => $locked->refunded_amount,
                    'adjustment_code' => $group,
                    'credit_note' => $refund->credit_note_number,
                ],
                reason: "Refund adjustment: {$reason}",
            );

            $refunded = $amount;
        });

        return $refunded;
    }

    /**
     * The amount that may still be refunded on a payment: the original amount
     * minus everything already refunded.
     */
    public function refundableAmount(Payment $payment): float
    {
        return round((float) $payment->amount - (float) $payment->refunded_amount, 2);
    }

    /**
     * Resolve a replay of an idempotency key.
     *
     * @return float|null the original refunded amount when the key was seen
     *                    before with the same payload, or null for a fresh call.
     */
    protected function resolveReplay(string $key, string $fingerprint): ?float
    {
        $existing = Refund::query()->where('idempotency_key', $key)->first();

        if ($existing === null) {
            return null;
        }

        if (! hash_equals((string) $existing->idempotency_hash, $fingerprint)) {
            abort(409, 'This idempotency key was already used with a different payload.');
        }

        return (float) $existing->amount;
    }

    /**
     * A stable fingerprint of the refund request, so the same key with a
     * different amount, reason or fee policy is a conflict.
     */
    protected function fingerprint(Payment $payment, float $amount, string $reason, bool $reverseFee): string
    {
        return hash('sha256', json_encode([
            'payment_id' => $payment->id,
            'amount' => $amount,
            'reason' => $reason,
            'reverse_fee' => $reverseFee,
        ]));
    }

    protected function creditNoteNumber(): string
    {
        return 'CN-ETH-'.date('Y').'-'.strtoupper(substr(uniqid(), -6));
    }

    /**
     * Reverse the earning of any commission already paid out for this payment's
     * deal, so a refund after payout leaves a recovery (negative balance).
     */
    protected function recoverCommissions(Payment $payment, float $total, float $previouslyRefunded, float $cumulativelyRefunded, string $refundGroup, User $actor): void
    {
        foreach ($this->commissionsFor($payment) as $commission) {
            if ($commission->status !== 'paid') {
                continue;
            }

            $net = (float) $commission->net_payout_amount;

            if ($net <= 0) {
                continue;
            }

            $recovery = $this->proportionalReversal($net, $total, $previouslyRefunded, $cumulativelyRefunded);

            if ($recovery == 0.0) {
                continue;
            }

            $partyUserId = $commission->agent?->user_id;
            $group = 'commission-recovery:'.$commission->id.':'.$refundGroup;

            $this->ledger->post($group, [
                [
                    'entry_type' => 'debit',
                    'category' => 'agent_receivable',
                    'amount' => $recovery,
                    'currency' => 'ETB',
                    'description' => "Commission recovery receivable on refund of Payment #{$payment->transaction_number}",
                    'reference' => $payment->transaction_number,
                    'user_id' => $actor->id,
                    'party_user_id' => $partyUserId,
                ],
                [
                    'entry_type' => 'credit',
                    'category' => 'agent_commission',
                    'amount' => $recovery,
                    'currency' => 'ETB',
                    'description' => "Commission earning reversed for {$commission->voucher_number} after refund",
                    'reference' => $payment->transaction_number,
                    'user_id' => $actor->id,
                    'party_user_id' => $partyUserId,
                ],
            ]);

            AuditLog::log(
                user: $actor,
                action: 'commission.recovered',
                entity: $commission,
                oldValue: ['net_payout_amount' => $net],
                newValue: ['recovery' => $recovery, 'ledger_group' => $group],
                reason: "Recovery after refunding Payment #{$payment->transaction_number}",
            );
        }
    }

    /**
     * Commissions tied to the deal a payment paid for.
     *
     * @return Collection<int, Commission>
     */
    protected function commissionsFor(Payment $payment)
    {
        if ($payment->payable_id && str_contains((string) $payment->payable_type, 'Order')) {
            return Commission::query()->where('order_id', $payment->payable_id)->get();
        }

        if ($payment->payable_id && str_contains((string) $payment->payable_type, 'Lease')) {
            return Commission::query()->where('lease_id', $payment->payable_id)->get();
        }

        if ($payment->property_id) {
            return Commission::query()->where('property_id', $payment->property_id)->get();
        }

        return collect();
    }

    /**
     * The slice of a fee or tax to reverse for a refund, measured cumulatively
     * and differenced so several partial refunds always sum to the whole.
     */
    protected function proportionalReversal(float $total, float $amount, float $previouslyRefunded, float $cumulativelyRefunded): float
    {
        if ($amount <= 0 || $total == 0.0) {
            return 0.0;
        }

        $cumulative = round($total * ($cumulativelyRefunded / $amount), 2);
        $prior = round($total * ($previouslyRefunded / $amount), 2);

        return round($cumulative - $prior, 2);
    }
}
