# Ledger reconciliation

How the double-entry change (SRS §82, §171) moved the books from single-sided
entries to balanced journals, what was done to the rows that already existed,
and a line-by-line explanation of every difference in the seeded data.

**Old rows are never rewritten or deleted.** The upgrade only marks them; the
new writer only ever appends.

---

## 1. What changed

| | Legacy (before) | Current (after) |
|---|---|---|
| Ledger writer | each money path wrote its own leg(s) directly | one writer, `App\Services\LedgerService::post()`, which refuses an unbalanced posting |
| Grouping | none | every leg of an event shares a `transaction_group`, which is also the idempotency key |
| Balance | by convention | proven per `transaction_group`: debits = credits |
| Revenue credit | gross amount | gross **less** the platform fee **and** the VAT on that fee |
| Platform fee | mixed into the revenue leg | its own `platform_fee` credit |
| VAT | "included" in the fee (never posted) | its own `vat_payable` credit |
| Cash | implicit | an explicit `cash` debit on every money-in event |
| Withholding | a description on a commission | a `tax_withholding` credit, with the rate snapshotted |
| Rate history | today's rate applied to everything | the rate that produced an amount is snapshotted on the record |

Two new ledger accounts appear as a result: **`cash`** (money actually moved)
and **`vat_payable`** (VAT the platform owes the authority).

---

## 2. Marking the old rows

`database/migrations/2026_10_16_122000_add_is_legacy_to_financial_ledgers_table.php`
adds `is_legacy` (default `false`) and then, once, sets it to `true` for every
row that already exists:

```php
DB::table('financial_ledgers')->update(['is_legacy' => true]);
```

Nothing else about those rows is touched — not the amount, the category, the
date or the description. Every row written from here on defaults to
`is_legacy = false`, so "current" is the safe default and "legacy" is the
exception that has to be flagged.

---

## 3. The seeded data, before and after

Reproduce with a fresh demo database:

```bash
APP_ENV=local php artisan migrate:fresh --seed
```

### Before — the four seeded ledger rows as they were written

| # | Ledger code | Entry | Category | Amount (ETB) | Group | Legacy |
|---|---|---|---|---|---|---|
| 1 | LED-2026-FE71A1 | debit | booking_revenue | 85,000.00 | — | yes |
| 2 | LED-2026-00A6F6 | credit | rental_revenue | 400,000.00 | — | yes |
| 3 | LED-2026-029061 | credit | rental_revenue | 85,000.00 | — | yes |
| 4 | LED-2026-047732 | credit | booking_revenue | 85,000.00 | — | yes |

| Legacy totals | Amount (ETB) |
|---|---|
| Credits | 570,000.00 |
| Debits | 85,000.00 |
| **Net** | **485,000.00** |
| Rows with a `transaction_group` | 0 |
| `cash` legs | 0 |
| `vat_payable` legs | 0 |

The books do **not** balance: 570,000 of credits have no matching debits. That
is the whole reason for the change.

### After — the same rows, left untouched, plus what the current model writes

The four rows above are **not** changed. They remain, flagged legacy, at the
same amounts. What follows is the **restatement** of the three revenue credits
under the current rules (2% platform fee, 15% VAT on the fee), i.e. the journal
the platform writes today for the same money-in events:

| Source row | Cash debit | Revenue credit (net) | `platform_fee` credit | `vat_payable` credit |
|---|---|---|---|---|
| #2 rental 400,000 | 400,000.00 | 390,800.00 | 8,000.00 | 1,200.00 |
| #3 rental 85,000 | 85,000.00 | 83,045.00 | 1,700.00 | 255.00 |
| #4 booking 85,000 | 85,000.00 | 83,045.00 | 1,700.00 | 255.00 |
| **Total** | **570,000.00** | **556,890.00** | **11,400.00** | **1,710.00** |

Each restated journal balances: 556,890.00 + 11,400.00 + 1,710.00 = 570,000.00,
which equals the cash debit.

The lone legacy **debit** (row #1, 85,000.00 to `booking_revenue`) is a
reversal; under the current model it is written as a `cash` **credit** of
85,000.00 paired with the revenue debit, so it too balances.

---

## 4. Every difference, explained

1. **Revenue is now net, not gross.** Legacy rows #2–#4 credited 400,000 /
   85,000 / 85,000 as revenue. The current model credits 390,800 / 83,045 /
   83,045, because the platform fee and the VAT on it are taken out first. The
   570,000 of "revenue" in the legacy books overstated revenue by 13,110
   (11,400 fee + 1,710 VAT).
2. **The platform fee is its own account.** 11,400 that used to be hidden
   inside the revenue credit is now an explicit `platform_fee` credit. This is
   the revenue the platform actually keeps.
3. **VAT is its own liability.** 1,710 is now a `vat_payable` credit — money the
   platform collects on behalf of the authority, not its own income. The legacy
   books had no way to show it: the fee was treated as VAT-inclusive and the
   VAT was never posted.
4. **Cash is explicit.** The current model debits `cash` for the 570,000 that
   came in, so each event balances on its own. The legacy rows have no cash
   leg, which is why the legacy books do not balance.
5. **The lone legacy debit.** Row #1 (85,000 debit, no credit) becomes a cash
   credit of 85,000 under the current model. It is a legacy anomaly, not a new
   economic event.
6. **The net is unchanged in cash terms.** The platform received 570,000 and
   paid out 85,000 either way (net 485,000). The change does not alter the cash;
   it makes the *categories* tell the truth about what was revenue, what was
   fee, and what was tax.

---

## 5. How every report handles both kinds

**Chosen policy: legacy single-sided rows are kept out of net revenue and the
P&L, and shown as their own totals.** No opening-balance journal is posted and no
old row is rewritten. The alternative — posting one documented opening-balance
journal to bring the legacy rows into double entry — was rejected because it
would invent a balancing leg the original event never had.

- **Balance audit** (`tests/Feature/FinanceSettingsAndDoubleEntryLedgerTest.php`,
  `LegacyLedgerReconciliationTest.php`): groups by `transaction_group` and checks
  debits = credits. Legacy rows have no group, so they are excluded by
  construction and can never make the audit report a false imbalance.
- **Finance desk** (`FinancialReportingController::index`): the profit-and-loss
  figures (`gross_inflow`, `gross_outflow`, `net_retained`, and every category
  total such as `platform_fees`) are computed over **current** rows only
  (`is_legacy = false`). The legacy rows are surfaced separately as
  `legacy_ledger_entries`, `legacy_inflow` and `legacy_outflow`, so the reader
  sees them without them touching revenue or profit.
- **VAT provision**: read from the `vat_payable` account, not re-derived from
  today's rate. Legacy rows contribute nothing, which is correct — their VAT was
  never separated.
- **CSV export**: adds a `Books` column that reads `Legacy (single-sided)` or
  `Current (balanced journal)` per row.
- **Audit statement**: prints every row regardless of kind, so a printed
  statement still reconciles to the exported CSV.

No report re-derives an old amount from today's rate, and no report rewrites a
legacy row.
