<?php

namespace Database\Factories;

use App\Models\FinanceReviewFlag;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinanceReviewFlag>
 */
class FinanceReviewFlagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reviewable_type' => Payment::class,
            'reviewable_id' => 0,
            'payment_id' => Payment::factory(),
            'reason' => fake()->sentence(8),
            'resolved_at' => null,
            'resolved_by' => null,
        ];
    }
}
