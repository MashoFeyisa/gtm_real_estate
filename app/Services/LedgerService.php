<?php

namespace App\Services;

use App\Models\FinancialLedger;

/**
 * Posts balanced double-entry journals.
 *
 * Every economic event must debit exactly as much as it credits. The legs of a
 * single event share a `transaction_group`, which doubles as the idempotency
 * key (re-posting the same group is a no-op) and the unit the books are audited
 * against.
 */
class LedgerService
{
    /**
     * Post a balanced journal for one economic event.
     *
     * @param  string  $group  stable identifier for the event, e.g. `payment:12`
     * @param  array<int, array<string, mixed>>  $legs  each leg needs `entry_type`, `category` and `amount`
     */
    public function post(string $group, array $legs): void
    {
        $legs = array_values(array_filter(
            $legs,
            static fn (array $leg): bool => round((float) ($leg['amount'] ?? 0), 2) != 0.0
        ));

        if ($legs === []) {
            return;
        }

        if (FinancialLedger::query()->where('transaction_group', $group)->exists()) {
            return;
        }

        $debits = 0.0;
        $credits = 0.0;

        foreach ($legs as $leg) {
            if (($leg['entry_type'] ?? null) === 'debit') {
                $debits += (float) $leg['amount'];
            } elseif (($leg['entry_type'] ?? null) === 'credit') {
                $credits += (float) $leg['amount'];
            } else {
                throw new \InvalidArgumentException("Ledger leg for [{$group}] must be a debit or a credit.");
            }
        }

        $debits = round($debits, 2);
        $credits = round($credits, 2);

        if ($debits !== $credits) {
            throw new \RuntimeException("Unbalanced ledger posting [{$group}]: debits {$debits} do not equal credits {$credits}.");
        }

        foreach ($legs as $leg) {
            FinancialLedger::create($leg + ['transaction_group' => $group]);
        }
    }

    /**
     * Whether a balanced journal has already been posted for the group.
     */
    public function hasPosted(string $group): bool
    {
        return FinancialLedger::query()->where('transaction_group', $group)->exists();
    }
}
