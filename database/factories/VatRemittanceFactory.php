<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VatRemittance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VatRemittance>
 */
class VatRemittanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->randomElement([300, 1500, 4500]),
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'reference' => strtoupper(fake()->bothify('MOR-########')),
            'ledger_group' => null,
            'created_by' => User::factory(),
        ];
    }
}
