<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = fake()->randomElement(['Addis Ababa', 'Hawassa', 'Dire Dawa', 'Adama']);
        $district = fake()->randomElement(['Bole', 'Yeka', 'Kirkos', 'Kazanchis', 'Sarbet']);

        return [
            'name' => $district.' Branch ('.$city.')',
            'code' => strtoupper(fake()->unique()->bothify('BR-??#')),
            'city' => $city,
            'subcity' => $district,
            'address' => fake()->streetAddress(),
            'manager_name' => fake()->name(),
            'phone' => '+2519'.fake()->numerify('########'),
            'email' => fake()->unique()->companyEmail(),
            'status' => 'active',
            'is_headquarters' => false,
        ];
    }

    /**
     * Head office branch.
     */
    public function headquarters(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_headquarters' => true,
        ]);
    }
}
