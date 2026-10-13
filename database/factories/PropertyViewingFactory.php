<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyViewing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<PropertyViewing>
 */
class PropertyViewingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'agent_id' => null,
            'user_id' => null,
            'buyer_name' => fake()->name(),
            'buyer_email' => fake()->safeEmail(),
            'buyer_phone' => '+2519'.fake()->numerify('########'),
            'viewing_date' => Carbon::today()->addDays(fake()->numberBetween(1, 14))->toDateString(),
            'viewing_time' => fake()->randomElement(['09:30', '10:00', '11:30', '14:00', '15:30']),
            'viewing_type' => fake()->randomElement(['in_person', 'video', 'virtual']),
            'status' => 'requested',
            'buyer_notes' => fake()->boolean(40) ? fake()->sentence() : null,
            'agent_notes' => null,
        ];
    }

    /**
     * Viewing confirmed by the seller/agent.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    /**
     * Viewing already completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
