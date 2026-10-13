<?php

namespace Database\Factories;

use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<MaintenanceTicket>
 */
class MaintenanceTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = fake()->randomElement(['plumbing', 'electrical', 'internet', 'appliance', 'structural', 'cleaning', 'other']);

        return [
            'property_id' => Property::factory()->forRent(),
            'user_id' => User::factory(),
            'title' => ucwords(fake()->words(4, true)),
            'category' => $category,
            'priority' => fake()->randomElement(['low', 'medium', 'medium', 'high']),
            'status' => fake()->randomElement(['open', 'open', 'assigned', 'in_progress']),
            'description' => fake()->sentences(3, true),
            'location_notes' => fake()->boolean(50) ? fake()->sentence() : null,
            'scheduled_at' => fake()->boolean(40) ? Carbon::today()->addDays(fake()->numberBetween(1, 7)) : null,
            'resolved_at' => null,
            'assigned_to' => null,
            'resolution_notes' => null,
        ];
    }

    /**
     * Ticket already resolved by maintenance.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'resolved_at' => now()->subDays(fake()->numberBetween(1, 10)),
            'resolution_notes' => 'Issue inspected and fixed; tenant notified.',
        ]);
    }

    /**
     * Emergency priority ticket (e.g. burst pipe).
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'emergency',
            'status' => 'open',
        ]);
    }
}
