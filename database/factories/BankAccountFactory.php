<?php

namespace Database\Factories;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankAccount>
 */
class BankAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bank_name' => fake()->randomElement(['CBE Bank', 'Awash Bank', 'Dashen Bank', 'Bank of Abyssinia', 'Cooperative Bank of Oromia']),
            'account_name' => 'GTM Real Estate PLC',
            'account_number' => fake()->numerify('#### #### #### ##'),
            'branch' => fake()->city().' Branch',
            'type' => 'bank',
            'instructions' => 'Deposit directly or via mobile banking app.',
            'is_active' => true,
            'sort_order' => 1,
        ];
    }

    public function cbeBirr(): static
    {
        return $this->state(fn (array $attributes) => [
            'bank_name' => 'CBE Birr Merchant Account',
            'account_name' => 'GTM Real Estate Company',
            'account_number' => fake()->numerify('######'),
            'type' => 'cbe_birr',
            'instructions' => 'Transfer directly to GTM CBE Merchant Till / Shortcode.',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
