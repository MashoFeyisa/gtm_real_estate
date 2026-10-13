<?php

namespace Database\Factories;

use App\Models\DeveloperPaymentPlan;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeveloperPaymentPlan>
 */
class DeveloperPaymentPlanFactory extends Factory
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
            'installment_name' => fake()->randomElement([
                'Booking Deposit',
                'Foundation Stage',
                'Structural Frame',
                'Finishing Stage',
                'Handover Balance',
            ]),
            'percentage' => fake()->randomElement([10, 15, 20, 25, 30]),
            'milestone_trigger' => fake()->boolean(60) ? fake()->randomElement(['Foundation', 'Structure', 'Finishing', 'Handover']) : null,
            'order_index' => fake()->numberBetween(1, 5),
        ];
    }
}
