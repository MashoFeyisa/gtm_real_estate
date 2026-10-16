<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\Commission;
use App\Models\FinancialLedger;
use App\Models\PayoutRequest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Attribute existing ledger legs to the party they belong to (SRS §194).
 *
 * `financial_ledgers.party_user_id` was added after money had already been
 * written, so commission and payout legs created before it carry a null party.
 * Their balances would read as zero. This command fills that attribution from
 * the commission/payout the group belongs to.
 *
 * It is deliberately narrow and idempotent: it only touches rows whose
 * `party_user_id` is still null, and it only ever writes that one column - no
 * amount, entry type or category is changed, so the append-only rule on money
 * values is untouched. Run with --dry-run first.
 */
#[Signature('ledger:backfill-party
            {--dry-run : Report what would change without writing anything}')]
#[Description('Backfill party_user_id on existing commission/payout ledger legs')]
class LedgerBackfillPartyCommand extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $legs = FinancialLedger::query()
            ->whereNull('party_user_id')
            ->whereNotNull('transaction_group')
            ->where(function ($query): void {
                $query->where('transaction_group', 'like', 'commission:%')
                    ->orWhere('transaction_group', 'like', 'commission-recovery:%')
                    ->orWhere('transaction_group', 'like', 'payout:%');
            })
            ->get();

        if ($legs->isEmpty()) {
            $this->info('No unattributed commission/payout ledger legs found; nothing to backfill.');

            return self::SUCCESS;
        }

        $updated = 0;
        $unresolved = 0;

        foreach ($legs->groupBy('transaction_group') as $group => $groupLegs) {
            $partyId = $this->partyForGroup((string) $group);

            if ($partyId === null) {
                $unresolved += $groupLegs->count();

                continue;
            }

            if ($dryRun) {
                $this->line("[dry-run] {$group}: {$groupLegs->count()} leg(s) -> party_user_id {$partyId}");

                continue;
            }

            // Mass update: bypasses the model's immutability guard because this
            // is attribution metadata, not a money value. Only null rows match.
            FinancialLedger::query()
                ->whereIn('id', $groupLegs->pluck('id'))
                ->update(['party_user_id' => $partyId]);

            $updated += $groupLegs->count();
        }

        if ($dryRun) {
            $this->info("Dry run: {$legs->count()} leg(s) would be attributed; {$unresolved} could not be resolved.");
        } else {
            $this->info("Backfilled {$updated} ledger leg(s); {$unresolved} could not be resolved.");
        }

        return self::SUCCESS;
    }

    /**
     * The party a ledger group belongs to, or null when it cannot be resolved.
     */
    private function partyForGroup(string $group): ?int
    {
        if (str_starts_with($group, 'payout:')) {
            $payoutId = explode(':', $group)[1] ?? null;

            return $payoutId ? PayoutRequest::query()->whereKey($payoutId)->value('user_id') : null;
        }

        if (str_starts_with($group, 'commission-recovery:')) {
            $commissionId = explode(':', $group)[1] ?? null;

            return $commissionId ? $this->agentUserIdForCommission($commissionId) : null;
        }

        if (str_starts_with($group, 'commission:')) {
            $commissionId = explode(':', $group)[1] ?? null;

            return $commissionId ? $this->agentUserIdForCommission($commissionId) : null;
        }

        return null;
    }

    private function agentUserIdForCommission(string $commissionId): ?int
    {
        $agentId = Commission::query()->whereKey($commissionId)->value('agent_id');

        return $agentId ? Agent::query()->whereKey($agentId)->value('user_id') : null;
    }
}
