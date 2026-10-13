<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<PropertyOffer>
 */
class PropertyOfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalPrice = fake()->randomElement([2500000, 4800000, 8500000, 15000000, 32000000]);
        $offeredPrice = (int) round($originalPrice * fake()->randomFloat(2, 0.85, 0.98) / 1000) * 1000;

        return [
            'property_id' => Property::factory(),
            'agent_id' => null,
            'user_id' => User::factory(),
            'buyer_name' => fake()->name(),
            'buyer_email' => fake()->safeEmail(),
            'buyer_phone' => '+2519'.fake()->numerify('########'),
            'offer_type' => 'purchase',
            'original_price' => $originalPrice,
            'offered_price' => $offeredPrice,
            'currency' => 'ETB',
            'financing_type' => fake()->randomElement(['cash', 'bank_loan', 'mortgage']),
            'deposit_proposed' => round($offeredPrice * 0.10, 2),
            'valid_until' => Carbon::today()->addDays(fake()->numberBetween(3, 21)),
            'status' => 'submitted',
            'current_counter_amount' => null,
            'latest_counter_by' => null,
            'message' => fake()->boolean(60) ? fake()->sentence() : null,
            'order_id' => null,
            'lease_id' => null,
            'closing_status' => 'pending',
            'closing_notes' => null,
        ];
    }

    /**
     * Offer that has been countered by the seller/agent.
     */
    public function countered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'countered',
            'current_counter_amount' => round(((int) $attributes['original_price'] + (int) $attributes['offered_price']) / 2, 2),
            'latest_counter_by' => 'seller_agent',
        ]);
    }

    /**
     * Offer accepted and moving toward closing.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'current_counter_amount' => $attributes['offered_price'],
            'latest_counter_by' => 'seller_agent',
        ]);
    }

    /**
     * Offer on a rental property.
     */
    public function forRental(): static
    {
        return $this->state(fn (array $attributes) => [
            'offer_type' => 'rental',
        ]);
    }
}
