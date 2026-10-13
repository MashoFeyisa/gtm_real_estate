<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = Carbon::today()->addDays(fake()->numberBetween(3, 30));
        $nights = fake()->numberBetween(1, 7);
        $checkOut = $checkIn->copy()->addDays($nights);
        $nightly = fake()->randomElement([1800, 3200, 4500, 6800]);
        $accommodation = $nightly * $nights;
        $cleaning = fake()->randomElement([400, 600, 850]);
        $service = round($accommodation * 0.05);

        return [
            'property_id' => Property::factory()->forStay(),
            'listing_id' => null,
            'room_number' => null,
            'user_id' => User::factory(),
            'guest_name' => fake()->name(),
            'guest_email' => fake()->safeEmail(),
            'guest_phone' => '+2519'.fake()->numerify('########'),
            'guest_id_number' => null,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'total_nights' => $nights,
            'guests_count' => fake()->numberBetween(1, 4),
            'nightly_price' => $nightly,
            'accommodation_total' => $accommodation,
            'cleaning_fee' => $cleaning,
            'service_fee' => $service,
            'security_deposit' => fake()->randomElement([0, 1500, 3000]),
            'total_amount' => $accommodation + $cleaning + $service,
            'currency' => 'ETB',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'special_requests' => fake()->boolean(30) ? fake()->sentence() : null,
            'host_notes' => null,
        ];
    }

    /**
     * Upcoming confirmed stay.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_date' => Carbon::today()->addWeek()->toDateString(),
            'check_out_date' => Carbon::today()->addDays(10)->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Currently checked-in guest.
     */
    public function checkedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_date' => Carbon::today()->subDays(2)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(2)->toDateString(),
            'checked_in_at' => Carbon::today()->subDays(2)->setTime(14, 5),
            'status' => 'checked_in',
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Completed past stay (eligible for reviews).
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_date' => Carbon::today()->subMonths(2)->toDateString(),
            'check_out_date' => Carbon::today()->subMonths(2)->addDays(4)->toDateString(),
            'checked_in_at' => Carbon::today()->subMonths(2)->setTime(14, 0),
            'checked_out_at' => Carbon::today()->subMonths(2)->addDays(4)->setTime(11, 0),
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Booking awaiting payment confirmation (finance audit queue).
     */
    public function awaitingPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'awaiting_confirmation',
        ]);
    }
}
