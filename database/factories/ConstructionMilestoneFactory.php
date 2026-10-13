<?php

namespace Database\Factories;

use App\Models\ConstructionMilestone;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConstructionMilestone>
 */
class ConstructionMilestoneFactory extends Factory
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
            'milestone_name' => fake()->randomElement([
                'Excavation & Foundation',
                'Structural Skeleton',
                'Block Masonry & Facade',
                'MEP, Electrical & Plumbing',
                'Interior Finishing & Glazing',
                'Landscaping & Handover',
            ]),
            'percentage' => fake()->numberBetween(0, 100),
            'target_date' => 'Q'.fake()->numberBetween(1, 4).' '.now()->addYear()->year,
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed']),
            'order_index' => fake()->numberBetween(1, 6),
        ];
    }

    /**
     * Milestone fully completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'percentage' => 100,
            'status' => 'completed',
        ]);
    }
}
