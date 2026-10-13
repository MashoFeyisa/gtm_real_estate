<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Commission;
use App\Models\Lease;
use App\Models\Order;
use App\Models\Property;

class CommissionSplitService
{
    /**
     * Standard Ethiopian Multi-Tier Brokerage Compensation Tiers.
     *
     * @var array<string, array{name: string, amharic: string, default_split: float, badge_class: string}>
     */
    public const TIERS = [
        'junior' => [
            'name' => 'Junior Associate',
            'amharic' => 'ጀማሪ ደላላ',
            'default_split' => 60.00,
            'agency_split' => 40.00,
            'badge_class' => 'bg-slate-100 text-slate-700 border-slate-300',
        ],
        'senior' => [
            'name' => 'Senior Consultant',
            'amharic' => 'ከፍተኛ አማካሪ',
            'default_split' => 70.00,
            'agency_split' => 30.00,
            'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        ],
        'principal' => [
            'name' => 'Principal Partner',
            'amharic' => 'ዋና አጋር ደላላ',
            'default_split' => 80.00,
            'agency_split' => 20.00,
            'badge_class' => 'bg-[#102b25] text-[#d4af37] border-[#d4af37]',
        ],
    ];

    /**
     * Get tier configuration details.
     *
     * @return array{name: string, amharic: string, default_split: float, agency_split: float, badge_class: string}
     */
    public function getTierDetails(?string $tier): array
    {
        $key = strtolower($tier ?: 'junior');

        return self::TIERS[$key] ?? self::TIERS['junior'];
    }

    /**
     * Calculate multi-party commission split with Ethiopian 2% Withholding Tax.
     *
     * @return array<string, mixed>
     */
    public function calculateSplits(
        float $dealPrice,
        Agent $listingAgent,
        ?Agent $sellingAgent = null,
        ?Agent $referralAgent = null,
        float $commissionRate = 2.00,
        float $referralPercent = 10.00
    ): array {
        $totalCommissionPool = round($dealPrice * ($commissionRate / 100), 2);

        // 1. Referral Bonus Deduction
        $referralCut = 0.00;
        if ($referralAgent && $referralAgent->id !== $listingAgent->id) {
            $referralCut = round($totalCommissionPool * ($referralPercent / 100), 2);
        }

        $remainingPool = round($totalCommissionPool - $referralCut, 2);

        // 2. Determine Split Type
        $isCoBrokered = $sellingAgent && $sellingAgent->id !== $listingAgent->id;
        $splitType = $isCoBrokered ? 'co_brokered' : ($referralAgent ? 'referral' : 'single_agent');

        $listingSplitPct = (float) ($listingAgent->split_percentage ?: $this->getTierDetails($listingAgent->tier)['default_split']);

        if ($isCoBrokered) {
            $sellingSplitPct = (float) ($sellingAgent->split_percentage ?: $this->getTierDetails($sellingAgent->tier)['default_split']);

            // 50/50 split of remaining pool between listing & selling sides
            $listingSidePool = round($remainingPool / 2, 2);
            $sellingSidePool = round($remainingPool - $listingSidePool, 2);

            $listingAgentAmount = round($listingSidePool * ($listingSplitPct / 100), 2);
            $listingAgencyAmount = round($listingSidePool - $listingAgentAmount, 2);

            $sellingAgentAmount = round($sellingSidePool * ($sellingSplitPct / 100), 2);
            $sellingAgencyAmount = round($sellingSidePool - $sellingAgentAmount, 2);

            $companyRetained = round($listingAgencyAmount + $sellingAgencyAmount, 2);
        } else {
            $sellingSplitPct = 0.00;
            $sellingAgentAmount = 0.00;

            $listingAgentAmount = round($remainingPool * ($listingSplitPct / 100), 2);
            $companyRetained = round($remainingPool - $listingAgentAmount, 2);
        }

        // 3. Ethiopian 2% Withholding Tax (WHT) calculations
        $whtRate = 2.00;
        $listingWht = round($listingAgentAmount * ($whtRate / 100), 2);
        $listingNet = round($listingAgentAmount - $listingWht, 2);

        $sellingWht = round($sellingAgentAmount * ($whtRate / 100), 2);
        $sellingNet = round($sellingAgentAmount - $sellingWht, 2);

        $referralWht = round($referralCut * ($whtRate / 100), 2);
        $referralNet = round($referralCut - $referralWht, 2);

        return [
            'deal_price' => $dealPrice,
            'commission_rate' => $commissionRate,
            'total_commission_pool' => $totalCommissionPool,
            'split_type' => $splitType,
            'company_retained' => $companyRetained,
            'withholding_tax_rate' => $whtRate,

            'listing_agent' => [
                'agent_id' => $listingAgent->id,
                'name' => $listingAgent->name,
                'tier' => $listingAgent->tier ?: 'junior',
                'split_percentage' => $listingSplitPct,
                'gross_commission' => $listingAgentAmount,
                'withholding_tax' => $listingWht,
                'net_payout' => $listingNet,
            ],

            'selling_agent' => $isCoBrokered ? [
                'agent_id' => $sellingAgent->id,
                'name' => $sellingAgent->name,
                'tier' => $sellingAgent->tier ?: 'junior',
                'split_percentage' => $sellingSplitPct,
                'gross_commission' => $sellingAgentAmount,
                'withholding_tax' => $sellingWht,
                'net_payout' => $sellingNet,
            ] : null,

            'referral_agent' => $referralCut > 0 ? [
                'agent_id' => $referralAgent->id,
                'name' => $referralAgent->name,
                'tier' => $referralAgent->tier ?: 'junior',
                'referral_rate' => $referralPercent,
                'gross_commission' => $referralCut,
                'withholding_tax' => $referralWht,
                'net_payout' => $referralNet,
            ] : null,
        ];
    }

    /**
     * Record an auditable multi-tier commission deal.
     */
    public function recordDealCommission(
        Property $property,
        Agent $listingAgent,
        float $dealPrice,
        ?Agent $sellingAgent = null,
        ?Agent $referralAgent = null,
        ?Order $order = null,
        ?Lease $lease = null,
        float $commissionRate = 2.00
    ): Commission {
        $splits = $this->calculateSplits(
            $dealPrice,
            $listingAgent,
            $sellingAgent,
            $referralAgent,
            $commissionRate
        );

        $listingData = $splits['listing_agent'];

        return Commission::create([
            'agent_id' => $listingAgent->id,
            'property_id' => $property->id,
            'order_id' => $order?->id,
            'lease_id' => $lease?->id,
            'property_price' => $dealPrice,
            'commission_rate' => $commissionRate,
            'commission_amount' => $listingData['gross_commission'],
            'withholding_tax_rate' => 2.00,
            'withholding_tax_amount' => $listingData['withholding_tax'],
            'net_payout_amount' => $listingData['net_payout'],
            'status' => 'pending',
            'split_type' => $splits['split_type'],
            'total_deal_commission' => $splits['total_commission_pool'],
            'listing_agent_id' => $listingAgent->id,
            'selling_agent_id' => $splits['selling_agent']['agent_id'] ?? null,
            'referral_agent_id' => $splits['referral_agent']['agent_id'] ?? null,
            'listing_agent_amount' => $listingData['gross_commission'],
            'selling_agent_amount' => $splits['selling_agent']['gross_commission'] ?? 0.00,
            'referral_amount' => $splits['referral_agent']['gross_commission'] ?? 0.00,
            'company_retained_amount' => $splits['company_retained'],
            'agent_tier_applied' => $listingAgent->tier ?: 'junior',
            'split_breakdown' => $splits,
        ]);
    }
}
