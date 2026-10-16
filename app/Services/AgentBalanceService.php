<?php

namespace App\Services;

use App\Models\FinancialLedger;
use App\Models\PayoutRequest;
use App\Models\User;

/**
 * The available balance of an agent, host or professional, derived from the
 * ledger (SRS §194).
 *
 * A balance is never stored as a number. It is the money recognised for the
 * party (the commission/payout expense attributed to them) minus the tax
 * withheld on their behalf and the net cash already paid to them, less anything
 * held back: pending or approved payout requests, and the configured holdback
 * for possible refunds.
 *
 * When a payment is refunded after the commission was already paid out, a
 * recovery reverses the party's earning, so this balance goes negative. A
 * negative balance is a recovery the next payout must clear.
 */
class AgentBalanceService
{
    /**
     * Categories that recognise money earned by the party (the expense side of
     * a commission or a payout).
     *
     * @var array<int, string>
     */
    public const EARNING_CATEGORIES = ['agent_commission', 'payout'];

    public function __construct(private FinanceSettings $settings) {}

    /**
     * The ledger-derived available balance for a party, in ETB.
     */
    public function availableFor(User $party): float
    {
        return round($this->grossBalanceFor($party) - $this->heldFor($party), 2);
    }

    /**
     * Money earned, net of tax withheld and net cash already paid, before any
     * holds. This may be negative after a recovery.
     */
    public function grossBalanceFor(User $party): float
    {
        $legs = FinancialLedger::query()
            ->where('party_user_id', $party->id)
            ->where('is_legacy', false);

        $earned = (float) (clone $legs)->where('entry_type', 'debit')
            ->whereIn('category', self::EARNING_CATEGORIES)->sum('amount');
        $earnedReversed = (float) (clone $legs)->where('entry_type', 'credit')
            ->whereIn('category', self::EARNING_CATEGORIES)->sum('amount');
        $withheld = (float) (clone $legs)->where('entry_type', 'credit')
            ->where('category', 'tax_withholding')->sum('amount');
        $paid = (float) (clone $legs)->where('entry_type', 'credit')
            ->where('category', 'cash')->sum('amount');

        return round($earned - $earnedReversed - $withheld - $paid, 2);
    }

    /**
     * Amounts held against the party: pending/approved payout requests and the
     * configured holdback for possible refunds.
     */
    public function heldFor(User $party): float
    {
        $pending = (float) PayoutRequest::query()
            ->where('user_id', $party->id)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('gross_amount');

        $gross = $this->grossBalanceFor($party);
        $holdback = $gross > 0
            ? round($gross * $this->settings->payoutHoldbackRate() / 100, 2)
            : 0.0;

        return round($pending + $holdback, 2);
    }

    /**
     * The amount that may actually be requested right now: the available
     * balance when positive, otherwise zero.
     */
    public function payoutableFor(User $party): float
    {
        return max(0.0, $this->availableFor($party));
    }

    /**
     * Whether the party has enough for a payout request of `$amount`, given the
     * configured minimum threshold.
     */
    public function canRequest(User $party, float $amount): bool
    {
        $amount = round($amount, 2);

        return $amount >= $this->settings->payoutMinimumThreshold()
            && $amount <= $this->payoutableFor($party);
    }
}
