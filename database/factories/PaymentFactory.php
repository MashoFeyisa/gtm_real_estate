<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomElement([1500, 4500, 18000, 35000, 85000, 450000]);
        $platformFee = round($amount * 0.05, 2);

        return [
            'payable_type' => null,
            'payable_id' => null,
            'property_id' => fake()->boolean(60) ? Property::factory() : null,
            'user_id' => User::factory(),
            'payer_name' => fake()->name(),
            'payer_email' => fake()->safeEmail(),
            'payer_phone' => '+2519'.fake()->numerify('########'),
            'payment_type' => fake()->randomElement(['booking', 'rent_installment', 'earnest_deposit', 'stay_booking']),
            'payment_method' => fake()->randomElement(['bank_transfer', 'cbe_birr', 'telebirr', 'cash']),
            'bank_name' => fake()->randomElement(['CBE', 'Awash', 'Dashen', 'NIB']),
            'bank_account_number' => null,
            'bank_transaction_reference' => fake()->boolean(40) ? strtoupper(fake()->bothify('TXN-########')) : null,
            'deposit_slip_path' => null,
            'currency' => 'ETB',
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'tax_amount' => 0,
            'net_amount' => $amount - $platformFee,
            'status' => 'confirmed',
            'notes' => fake()->boolean(30) ? fake()->sentence() : null,
            'admin_notes' => null,
            'verified_at' => now(),
            'verified_by' => null,
            'payment_metadata' => [],
        ];
    }

    /**
     * Payment awaiting finance desk confirmation (bank transfer submitted).
     */
    public function awaitingConfirmation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'verified_at' => null,
        ]);
    }

    /**
     * Payment tied to a specific booking (morph).
     */
    public function forBooking(Booking $booking): static
    {
        return $this->state(fn (array $attributes) => [
            'payable_type' => $booking->getMorphClass(),
            'payable_id' => $booking->id,
            'property_id' => $booking->property_id,
            'payment_type' => 'stay_booking',
            'amount' => $booking->total_amount,
        ]);
    }
}
