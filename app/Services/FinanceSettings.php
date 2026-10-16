<?php

namespace App\Services;

use App\Models\CommissionRule;
use App\Models\FinanceRate;
use App\Models\SiteSetting;
use Carbon\CarbonInterface;

/**
 * Single source of truth for every configurable money rate (SRS §171, §195).
 *
 * Resolution order for a rate at a point in time:
 *
 *   1. the newest dated `finance_rates` row whose window covers that moment
 *      (the database tax engine, SRS §171);
 *   2. the current `site_settings` value (the pre-effective-date settings);
 *   3. the shipped default, mirroring the historic hard-coded value.
 *
 * Nothing in a money path may hard-code a fee, VAT, withholding or commission
 * rate, and nothing may re-derive an old amount from today's rate - the rate
 * that produced a stored amount is snapshotted on the record itself.
 */
class FinanceSettings
{
    public const PLATFORM_FEE_RATE = 'finance.platform_fee_rate';

    public const VAT_RATE = 'finance.vat_rate';

    public const WITHHOLDING_TAX_RATE = 'finance.withholding_tax_rate';

    public const DEFAULT_COMMISSION_RATE = 'finance.default_commission_rate';

    public const EXPERIENCE_PLATFORM_FEE_RATE = 'finance.experience_platform_fee_rate';

    public const REQUIRE_SECOND_APPROVAL = 'finance.rates.require_second_approval';

    public const PAYOUT_MINIMUM_THRESHOLD = 'finance.payout_minimum_threshold';

    public const PAYOUT_SCHEDULE = 'finance.payout_schedule';

    public const PAYOUT_HOLDBACK_RATE = 'finance.payout_holdback_rate';

    public const PAYOUT_HOLDBACK_DAYS = 'finance.payout_holdback_days';

    /**
     * Whether a rate change needs a second, different finance user to approve it
     * before it can move money. Off by default.
     */
    public function requiresSecondRateApproval(): bool
    {
        return filter_var(SiteSetting::get(self::REQUIRE_SECOND_APPROVAL, false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * The smallest balance that may be requested as a payout (ETB).
     */
    public function payoutMinimumThreshold(): float
    {
        return (float) SiteSetting::get(self::PAYOUT_MINIMUM_THRESHOLD, 500.0);
    }

    /**
     * When a payout may be requested (SRS §194): immediately, after_check_in,
     * after_check_out, weekly, monthly.
     */
    public function payoutSchedule(): string
    {
        $value = (string) SiteSetting::get(self::PAYOUT_SCHEDULE, 'immediately');

        return in_array($value, ['immediately', 'after_check_in', 'after_check_out', 'weekly', 'monthly'], true)
            ? $value
            : 'immediately';
    }

    /**
     * The share of a payout held back for possible refunds (percent).
     */
    public function payoutHoldbackRate(): float
    {
        return (float) SiteSetting::get(self::PAYOUT_HOLDBACK_RATE, 0.0);
    }

    /**
     * How long a holdback is retained before it becomes available (days).
     */
    public function payoutHoldbackDays(): int
    {
        return (int) SiteSetting::get(self::PAYOUT_HOLDBACK_DAYS, 7);
    }

    /**
     * Platform service fee rate charged on a gross payment (percent).
     */
    public function platformFeeRate(?CarbonInterface $at = null): float
    {
        return $this->rate(self::PLATFORM_FEE_RATE, FinanceRate::PLATFORM_FEE, 2.0, $at);
    }

    /**
     * VAT rate applied to the platform service fee (percent).
     */
    public function vatRate(?CarbonInterface $at = null): float
    {
        return $this->rate(self::VAT_RATE, FinanceRate::VAT, 15.0, $at);
    }

    /**
     * Ethiopian withholding tax rate deducted from a gross payout (percent).
     */
    public function withholdingTaxRate(?CarbonInterface $at = null): float
    {
        return $this->rate(self::WITHHOLDING_TAX_RATE, FinanceRate::WITHHOLDING, 2.0, $at);
    }

    /**
     * The one default commission rate (percent) used whenever no more specific
     * rule applies. Sale and rental deals share this default.
     */
    public function defaultCommissionRate(?CarbonInterface $at = null): float
    {
        return $this->rate(self::DEFAULT_COMMISSION_RATE, FinanceRate::COMMISSION, 5.0, $at);
    }

    /**
     * Platform fee rate (percent) applied to experience marketplace bookings.
     */
    public function experiencePlatformFeeRate(?CarbonInterface $at = null): float
    {
        return $this->rate(self::EXPERIENCE_PLATFORM_FEE_RATE, FinanceRate::EXPERIENCE_FEE, 15.0, $at);
    }

    /**
     * The commission rate for a deal: the most specific live override (agent,
     * then property type, then company), then an explicit per-agent rate, then
     * the shared default. An explicit rule always wins over the legacy
     * `agents.commission_rate` column so every override dimension stays live.
     */
    public function commissionRateFor(
        ?int $agentId = null,
        ?string $propertyType = null,
        ?int $companyId = null,
        ?float $agentRate = null,
        ?CarbonInterface $at = null
    ): float {
        $at = $at ?? now();

        return CommissionRule::overrideFor($agentId, $propertyType, $companyId, $at)
            ?? $agentRate
            ?? $this->defaultCommissionRate($at);
    }

    /**
     * Platform service fee charged on a gross amount.
     */
    public function platformFee(float $amount, ?CarbonInterface $at = null): float
    {
        return round($amount * $this->platformFeeRate($at) / 100, 2);
    }

    /**
     * VAT charged on top of the platform service fee.
     */
    public function vatOnFee(float $platformFee, ?CarbonInterface $at = null): float
    {
        return round($platformFee * $this->vatRate($at) / 100, 2);
    }

    /**
     * Withholding tax deducted from a gross payout.
     */
    public function withholdingTax(float $gross, ?CarbonInterface $at = null): float
    {
        return round($gross * $this->withholdingTaxRate($at) / 100, 2);
    }

    /**
     * The one and only net-amount formula: what the beneficiary keeps once the
     * platform service fee and the VAT charged on top of it are taken out of
     * the gross amount.
     */
    public function netAmount(float $amount, float $platformFee, float $vat = 0.0): float
    {
        return round($amount - $platformFee - $vat, 2);
    }

    /**
     * Commission owed on a price at the given rate (or the shared default).
     */
    public function commissionFor(float $price, ?float $rate = null): float
    {
        return round($price * ($rate ?? $this->defaultCommissionRate()) / 100, 2);
    }

    /**
     * The rate currently in force for a rate-book key, resolved exactly as a
     * money path would resolve it (dated rule, then setting, then default).
     */
    public function rateForRule(string $key, ?CarbonInterface $at = null): float
    {
        return match ($key) {
            FinanceRate::PLATFORM_FEE => $this->platformFeeRate($at),
            FinanceRate::VAT => $this->vatRate($at),
            FinanceRate::WITHHOLDING => $this->withholdingTaxRate($at),
            FinanceRate::COMMISSION => $this->defaultCommissionRate($at),
            FinanceRate::EXPERIENCE_FEE => $this->experiencePlatformFeeRate($at),
            default => 0.0,
        };
    }

    /**
     * Resolve a rate from the dated rule book, then settings, then the default.
     */
    private function rate(string $settingKey, string $ruleKey, float $default, ?CarbonInterface $at = null): float
    {
        $dated = FinanceRate::rateFor($ruleKey, $at);

        if ($dated !== null) {
            return $dated;
        }

        $value = SiteSetting::get($settingKey);

        if ($value === null || $value === '') {
            return $default;
        }

        return (float) $value;
    }
}
