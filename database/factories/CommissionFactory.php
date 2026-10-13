<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Commission;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commission>
 */
class CommissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $propertyPrice = fake()->randomElement([2500000, 8500000, 20000000]);
        $rate = fake()->randomElement([1.5, 2.0, 2.5, 3.0]);
        $amount = round($propertyPrice * $rate / 100, 2);
        $taxRate = 3.0;
        $tax = round($amount * $taxRate / 100, 2);

        return [
            'agent_id' => Agent::factory(),
            'order_id' => null,
            'lease_id' => null,
            'property_id' => Property::factory(),
            'property_price' => $propertyPrice,
            'commission_rate' => $rate,
            'commission_amount' => $amount,
            'withholding_tax_rate' => $taxRate,
            'withholding_tax_amount' => $tax,
            'net_payout_amount' => $amount - $tax,
            'payout_method' => fake()->randomElement(['bank_transfer', 'cbe_birr']),
            'payout_reference' => null,
            'status' => 'pending',
            'split_type' => 'single',
            'total_deal_commission' => $amount,
            'admin_note' => null,
            'paid_at' => null,
            'disbursed_by' => null,
            'voucher_number' => null,
            'listing_agent_id' => null,
            'selling_agent_id' => null,
            'referral_agent_id' => null,
            'listing_agent_amount' => null,
            'selling_agent_amount' => null,
            'referral_amount' => null,
            'company_retained_amount' => null,
            'agent_tier_applied' => null,
            'split_breakdown' => null,
        ];
    }

    /**
     * Commission settled and paid out to the agent.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now(),
            'payout_reference' => strtoupper(fake()->bothify('PO-########')),
        ]);
    }
}
