<?php

namespace Database\Factories;

use App\Models\OfferNegotiation;
use App\Models\PropertyOffer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfferNegotiation>
 */
class OfferNegotiationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_offer_id' => PropertyOffer::factory(),
            'sender_type' => fake()->randomElement(['buyer', 'seller_agent']),
            'amount' => fake()->randomElement([2200000, 4200000, 7800000, 14000000]),
            'currency' => 'ETB',
            'message' => fake()->sentence(10),
            'terms' => fake()->boolean(50) ? ['deposit' => '10%', 'closing' => '30 days'] : null,
        ];
    }

    /**
     * Counter offer from the seller/agent side.
     */
    public function sellerCounter(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_type' => 'seller_agent',
        ]);
    }
}
