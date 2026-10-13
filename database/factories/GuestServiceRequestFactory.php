<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\GuestServiceRequest;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestServiceRequest>
 */
class GuestServiceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => fake()->boolean(60) ? Booking::factory()->checkedIn() : null,
            'property_id' => Property::factory()->forStay(),
            'room_number' => fake()->randomElement(['101', '204', '305', '412']),
            'guest_name' => fake()->name(),
            'service_type' => fake()->randomElement([
                'room_cleaning',
                'extra_towels',
                'airport_transfer',
                'wake_up_call',
                'maintenance',
                'inquiry',
            ]),
            'priority' => fake()->randomElement(['normal', 'normal', 'high']),
            'description' => fake()->sentence(10),
            'status' => fake()->randomElement(['pending', 'pending', 'in_progress']),
            'handled_by' => fake()->boolean(40) ? User::factory()->state(['role' => 'property_manager']) : null,
            'response_notes' => null,
        ];
    }

    /**
     * Completed service request (housekeeping log).
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'response_notes' => 'Request fulfilled and confirmed with guest.',
        ]);
    }
}
