<?php

namespace Database\Seeders;

use App\Models\FinanceRate;
use Illuminate\Database\Seeder;

/**
 * Seed the baseline dated finance-rate book (SRS §171).
 *
 * The money paths fall back to shipped defaults when no dated rule exists, but
 * a real deployment should have an explicit, auditable baseline row for every
 * rate key. Each row is a dated fact effective from the platform's start date
 * and is approved, so `FinanceRate::rateFor()` resolves it. Seeding is
 * idempotent: a key that already has an approved row is left alone.
 */
class FinanceRateSeeder extends Seeder
{
    /**
     * The shipped default for each rate key, in percent.
     *
     * @var array<string, float>
     */
    public const BASELINE = [
        FinanceRate::PLATFORM_FEE => 2.0,
        FinanceRate::VAT => 15.0,
        FinanceRate::WITHHOLDING => 2.0,
        FinanceRate::COMMISSION => 5.0,
        FinanceRate::EXPERIENCE_FEE => 15.0,
    ];

    public function run(): void
    {
        foreach (self::BASELINE as $key => $rate) {
            $exists = FinanceRate::query()
                ->where('key', $key)
                ->where('status', FinanceRate::STATUS_APPROVED)
                ->exists();

            if ($exists) {
                continue;
            }

            FinanceRate::create([
                'key' => $key,
                'rate' => $rate,
                'effective_from' => '2020-01-01',
                'status' => FinanceRate::STATUS_APPROVED,
                'reason' => 'Baseline rate seeded from the shipped default.',
            ]);
        }
    }
}
