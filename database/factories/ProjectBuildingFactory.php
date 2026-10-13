<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectBuilding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectBuilding>
 */
class ProjectBuildingFactory extends Factory
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
            'name' => 'Building '.fake()->randomLetter(),
            'total_floors' => fake()->numberBetween(3, 18),
            'units_count' => fake()->numberBetween(4, 24),
            'description' => fake()->sentence(),
        ];
    }
}
