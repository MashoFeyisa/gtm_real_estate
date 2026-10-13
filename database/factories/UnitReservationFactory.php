<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectUnit;
use App\Models\UnitReservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitReservation>
 */
class UnitReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'unit_id' => ProjectUnit::factory(),
            'user_id' => fake()->boolean(70) ? User::factory() : null,
            'buyer_name' => fake()->name(),
            'buyer_email' => fake()->safeEmail(),
            'buyer_phone' => '+2519'.fake()->numerify('########'),
            'status' => fake()->randomElement(['pending', 'pending', 'confirmed']),
            'notes' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }

    /**
     * Reservation confirmed by the developer.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }
}
