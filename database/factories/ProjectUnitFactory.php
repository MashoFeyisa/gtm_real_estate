<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectUnit>
 */
class ProjectUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bedrooms = fake()->numberBetween(1, 4);

        return [
            'project_id' => Project::factory(),
            'building_id' => fake()->boolean(60) ? ProjectBuilding::factory() : null,
            'unit_number' => strtoupper(fake()->randomLetter()).fake()->numberBetween(101, 1804),
            'floor_number' => fake()->numberBetween(1, 18),
            'unit_type' => match ($bedrooms) {
                1 => '1 Bedroom',
                2 => '2 Bedroom',
                3 => '3 Bedroom',
                default => 'Penthouse',
            },
            'bedrooms' => $bedrooms,
            'bathrooms' => max(1, $bedrooms - 1),
            'area_sqm' => fake()->numberBetween(45, 220),
            'price' => fake()->randomElement([3500000, 7500000, 12500000, 22000000]),
            'currency' => 'ETB',
            'orientation' => fake()->randomElement(['North', 'South', 'East', 'West']),
            'parking_spaces' => fake()->numberBetween(0, 2),
            'status' => fake()->randomElement(['available', 'available', 'reserved', 'contracted', 'sold']),
            'floor_plan_image' => null,
            'features' => ['Balcony', 'Fitted Kitchen'],
        ];
    }

    /**
     * Unit still available for purchase.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
        ]);
    }
}
