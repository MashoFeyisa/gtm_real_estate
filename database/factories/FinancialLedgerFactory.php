<?php

namespace Database\Factories;

use App\Models\FinancialLedger;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialLedger>
 */
class FinancialLedgerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => fake()->boolean(70) ? Payment::factory() : null,
            'entry_type' => fake()->randomElement(['credit', 'debit']),
            'category' => fake()->randomElement([
                'booking_revenue',
                'rental_revenue',
                'earnest_deposit',
                'platform_fee',
                'agent_commission',
            ]),
            'amount' => fake()->randomElement([4500, 18000, 85000, 400000]),
            'currency' => 'ETB',
            'description' => fake()->sentence(8),
            'reference' => fake()->boolean(50) ? strtoupper(fake()->bothify('REF-########')) : null,
            'user_id' => fake()->boolean(40) ? User::factory() : null,
        ];
    }
}
