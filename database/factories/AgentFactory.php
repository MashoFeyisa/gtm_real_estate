<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agent>
 */
class AgentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'user_id' => User::factory()->state(['role' => 'contact']),
            'branch_id' => fake()->boolean(40) ? Branch::factory() : null,
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'password' => 'secret123',
            'phone' => '+2519'.fake()->numerify('########'),
            'bio' => fake()->sentence(12),
            'service_locations' => collect(fake()->randomElements(
                ['Addis Ababa', 'Bole', 'Kazanchis', 'Piassa', 'CMC', 'Ayat'],
                fake()->numberBetween(1, 3)
            ))->values()->all(),
            'languages' => fake()->randomElements(['Amharic', 'English', 'Afaan Oromoo', 'Tigrinya'], fake()->numberBetween(1, 3)),
            'phone_privacy' => fake()->randomElement(['show', 'hide', 'messaging_only']),
            'tier' => fake()->randomElement(['junior', 'senior', 'principal']),
            'split_percentage' => fake()->randomElement([50, 60, 70]),
            'license_number' => strtoupper(fake()->bothify('LIC-########')),
            'license_verified' => fake()->boolean(60),
            'is_active' => true,
            'commission_rate' => fake()->randomElement([3.0, 4.0, 5.0]),
            'photo_path' => null,
        ];
    }

    /**
     * Agent whose license is still pending verification (verification company queue).
     */
    public function pendingLicense(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_verified' => false,
            'tier' => 'junior',
        ]);
    }

    /**
     * Verified senior agent.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_verified' => true,
            'tier' => fake()->randomElement(['senior', 'principal']),
        ]);
    }
}
