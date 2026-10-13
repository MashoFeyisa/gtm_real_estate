<?php

namespace Database\Factories;

use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->boolean(70) ? '+2519'.fake()->numerify('########') : null,
            'subject' => fake()->randomElement([
                'Property Viewing Request',
                'Price Negotiation',
                'Rental Application Question',
                'Financing & Payment Plans',
                'Property Details Request',
            ]),
            'message' => fake()->paragraphs(2, true),
            'status' => fake()->randomElement(['new', 'new', 'contacted', 'resolved']),
            'read_at' => fake()->boolean(40) ? now()->subDays(fake()->numberBetween(0, 10)) : null,
            'agent_id' => null,
        ];
    }

    /**
     * Fresh unread inquiry (customer_support desk queue).
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
            'read_at' => null,
        ]);
    }
}
