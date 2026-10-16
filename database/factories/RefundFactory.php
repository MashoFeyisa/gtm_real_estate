<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Refund>
 */
class RefundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomElement([500, 1500, 4500, 18000]);

        return [
            'payment_id' => Payment::factory(),
            'amount' => $amount,
            'fee_reversed' => round($amount * 0.02, 2),
            'vat_reversed' => round($amount * 0.02 * 0.15, 2),
            'reason' => fake()->sentence(6),
            'ledger_group' => null,
            'credit_note_number' => null,
            'idempotency_key' => null,
            'idempotency_hash' => null,
            'created_by' => User::factory(),
        ];
    }
}
