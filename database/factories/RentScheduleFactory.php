<?php

namespace Database\Factories;

use App\Models\Lease;
use App\Models\RentSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentSchedule>
 */
class RentScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $month = now()->subMonths(fake()->numberBetween(0, 5));

        return [
            'lease_id' => Lease::factory(),
            'installment_number' => fake()->numberBetween(1, 12),
            'period_label' => $month->format('F Y'),
            'due_date' => $month->copy()->startOfMonth()->addDays(4)->toDateString(),
            'amount_due' => fake()->randomElement([18000, 35000, 55000, 85000]),
            'amount_paid' => 0,
            'status' => 'pending',
            'payment_id' => null,
            'paid_at' => null,
            'payment_method' => null,
            'notes' => null,
        ];
    }

    /**
     * Rent installment already settled by the tenant.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount_paid' => $attributes['amount_due'] ?? 0,
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => fake()->randomElement(['bank_transfer', 'cbe_birr', 'telebirr', 'cash']),
        ]);
    }
}
