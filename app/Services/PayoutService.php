<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\FinanceRate;
use App\Models\PayoutRequest;
use App\Models\Property;
use App\Models\User;
use App\Notifications\PayoutStatusNotification;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * The payout lifecycle (SRS §194).
 *
 * One writer for requesting, approving, rejecting and disbursing a payout, so
 * every desk follows the same rules:
 *
 *   * the available balance is ledger-derived (AgentBalanceService), never a
 *     stored number;
 *   * a request must clear the configured minimum threshold, the configurable
 *     schedule, and the available balance (less any holdback);
 *   * maker-checker: the requester can never approve or disburse their own
 *     payout;
 *   * withholding tax is applied, a voucher number is issued, and the bank
 *     reference is recorded;
 *   * real transfers are not integrated - a disbursement is marked a "manual
 *     disbursement";
 *   * the journal is balanced and idempotent per payout.
 */
class PayoutService
{
    public function __construct(
        private LedgerService $ledger,
        private AgentBalanceService $balances,
        private FinanceSettings $settings,
    ) {}

    /**
     * Whether the configured schedule allows a payout at this moment (SRS §194).
     */
    public function scheduleAllows(User $party, ?CarbonInterface $at = null): bool
    {
        $at = $at ?? now();

        return match ($this->settings->payoutSchedule()) {
            'weekly' => $at->isMonday(),
            'monthly' => (int) $at->day === 1,
            'after_check_in' => $this->hasStay($party, checkedOut: false),
            'after_check_out' => $this->hasStay($party, checkedOut: true),
            default => true,
        };
    }

    /**
     * Submit a payout request against the party's ledger-derived balance.
     */
    public function request(User $party, float $gross, string $method, ?string $bankName, ?string $accountNumber, string $role): PayoutRequest
    {
        $gross = round($gross, 2);

        abort_if(! $this->scheduleAllows($party), 422, 'Payouts are not open on the configured schedule right now.');
        abort_if($gross < $this->settings->payoutMinimumThreshold(), 422, 'Payout amount is below the minimum threshold of '.number_format($this->settings->payoutMinimumThreshold(), 2).' ETB.');

        $available = $this->balances->payoutableFor($party);

        abort_if($gross - $available > 0.004, 422, 'Payout exceeds the available balance of '.number_format($available, 2).' ETB.');

        $withholding = $this->settings->withholdingTax($gross);
        $net = round($gross - $withholding, 2);

        return DB::transaction(function () use ($party, $gross, $method, $bankName, $accountNumber, $role, $withholding, $net) {
            $payout = PayoutRequest::create([
                'payout_code' => 'PAY-REQ-'.strtoupper(substr(uniqid(), -8)),
                'user_id' => $party->id,
                'role' => $role,
                'gross_amount' => $gross,
                'withholding_tax' => $withholding,
                'net_amount' => $net,
                'payment_method' => $method,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'status' => 'pending',
                'disbursement_mode' => 'manual',
            ]);

            AuditLog::log(
                user: $party,
                action: 'payout.requested',
                entity: $payout,
                oldValue: null,
                newValue: ['gross_amount' => $gross, 'net_amount' => $net],
                reason: 'User submitted payout request',
            );

            $party->notify(new PayoutStatusNotification($payout, 'requested'));

            return $payout;
        });
    }

    /**
     * Approve a payout. Maker-checker: the requester can never approve it.
     */
    public function approve(User $actor, PayoutRequest $payout): PayoutRequest
    {
        abort_if((int) $payout->user_id === (int) $actor->id, 403, 'The requester of a payout cannot approve it.');
        abort_if($payout->status !== 'pending', 422, 'Only a pending payout can be approved.');

        $payout->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
            'processed_by' => $actor->id,
        ]);

        AuditLog::log(
            user: $actor,
            action: 'payout.approved',
            entity: $payout,
            oldValue: ['status' => 'pending'],
            newValue: ['status' => 'approved', 'approved_by' => $actor->id],
            reason: 'Finance approved a payout request',
        );

        $payout->user?->notify(new PayoutStatusNotification($payout, 'approved'));

        return $payout;
    }

    public function reject(User $actor, PayoutRequest $payout, ?string $reason = null): PayoutRequest
    {
        abort_if($payout->status === 'disbursed', 422, 'A disbursed payout cannot be rejected.');

        $payout->update([
            'status' => 'rejected',
            'rejection_reason' => $reason ?: 'Rejected by finance audit.',
            'processed_by' => $actor->id,
            'processed_at' => now(),
        ]);

        AuditLog::log(
            user: $actor,
            action: 'payout.rejected',
            entity: $payout,
            oldValue: ['status' => 'pending'],
            newValue: ['status' => 'rejected', 'reason' => $payout->rejection_reason],
            reason: 'Finance rejected a payout request',
        );

        $payout->user?->notify(new PayoutStatusNotification($payout, 'rejected'));

        return $payout;
    }

    /**
     * Disburse an approved payout (manual disbursement - no bank integration).
     */
    public function disburse(User $actor, PayoutRequest $payout, ?string $reference = null, ?string $idempotencyKey = null): PayoutRequest
    {
        abort_if((int) $payout->user_id === (int) $actor->id, 403, 'The requester of a payout cannot disburse it.');
        abort_if($payout->status === 'pending', 422, 'A payout must be approved before it is disbursed.');
        abort_if($payout->status === 'rejected', 422, 'A rejected payout cannot be disbursed.');

        $group = 'payout:'.$payout->id;

        // A retried disbursement must not pay twice.
        if ($this->ledger->hasPosted($group)) {
            return $payout;
        }

        DB::transaction(function () use ($actor, $payout, $reference, $idempotencyKey) {
            $voucher = $payout->voucher_number ?: 'VCH-PAY-'.date('Y').'-'.strtoupper(substr(uniqid(), -6));

            $payout->update([
                'status' => 'disbursed',
                'voucher_number' => $voucher,
                'disbursement_mode' => 'manual',
                'transaction_reference' => $reference ?: 'MANUAL-'.date('YmdHis'),
                'idempotency_key' => $idempotencyKey,
                'processed_by' => $actor->id,
                'processed_at' => now(),
            ]);

            $gross = (float) $payout->gross_amount;
            $net = (float) $payout->net_amount;
            $withholding = round($gross - $net, 2);
            $withholdingRate = $gross > 0 ? round($withholding / $gross * 100, 4) : 0.0;

            $this->ledger->post('payout:'.$payout->id, [
                [
                    'ledger_code' => 'LED-OUT-'.strtoupper(substr(uniqid(), -8)),
                    'entry_type' => 'debit',
                    'category' => 'payout',
                    'amount' => $gross,
                    'currency' => 'ETB',
                    'description' => "Manual payout disbursement #{$payout->payout_code} to user #{$payout->user_id}",
                    'reference' => $payout->transaction_reference,
                    'user_id' => $actor->id,
                    'party_user_id' => $payout->user_id,
                ],
                [
                    'entry_type' => 'credit',
                    'category' => 'cash',
                    'amount' => $net,
                    'currency' => 'ETB',
                    'description' => "Net payout paid for #{$payout->payout_code}",
                    'reference' => $payout->transaction_reference,
                    'user_id' => $actor->id,
                    'party_user_id' => $payout->user_id,
                ],
                [
                    'entry_type' => 'credit',
                    'category' => 'tax_withholding',
                    'amount' => $withholding,
                    'rate_snapshot' => ['key' => FinanceRate::WITHHOLDING, 'rate' => $withholdingRate],
                    'currency' => 'ETB',
                    'description' => "Withholding tax on payout #{$payout->payout_code}",
                    'reference' => $payout->transaction_reference,
                    'user_id' => $actor->id,
                    'party_user_id' => $payout->user_id,
                ],
            ]);

            AuditLog::log(
                user: $actor,
                action: 'payout.disbursed',
                entity: $payout,
                oldValue: ['status' => 'approved'],
                newValue: [
                    'status' => 'disbursed',
                    'voucher' => $voucher,
                    'reference' => $payout->transaction_reference,
                    'mode' => 'manual',
                ],
                reason: 'Manual disbursement recorded by finance',
            );

            $payout->user?->notify(new PayoutStatusNotification($payout, 'paid'));
        });

        return $payout->fresh();
    }

    /**
     * Whether the party has a stay that has started (checked in) or finished.
     */
    protected function hasStay(User $party, bool $checkedOut): bool
    {
        $propertyIds = Property::query()->where('user_id', $party->id)->pluck('id');

        if ($propertyIds->isEmpty()) {
            return false;
        }

        return Booking::query()
            ->whereIn('property_id', $propertyIds)
            ->when(
                $checkedOut,
                fn ($query) => $query->where('check_out_date', '<', now()->toDateString()),
                fn ($query) => $query->where('check_in_date', '<=', now()->toDateString()),
            )
            ->exists();
    }
}
