<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Lease;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Lease>
 */
class LeaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::today()->subMonths(fake()->numberBetween(1, 6))->startOfMonth();
        $monthlyRent = fake()->randomElement([18000, 35000, 55000, 85000]);

        return [
            'property_id' => Property::factory()->forRent(),
            'agent_id' => Agent::factory(),
            'landlord_user_id' => User::factory(),
            'tenant_user_id' => User::factory(),
            'tenant_name' => fake()->name(),
            'tenant_email' => fake()->safeEmail(),
            'tenant_phone' => '+2519'.fake()->numerify('########'),
            'tenant_id_type' => 'National ID',
            'tenant_id_number' => fake()->numerify('####??#####'),
            'tenant_occupation' => fake()->jobTitle(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => '+2519'.fake()->numerify('########'),
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addYear()->subDay()->toDateString(),
            'duration_months' => 12,
            'monthly_rent' => $monthlyRent,
            'security_deposit' => $monthlyRent * 2,
            'payment_frequency' => 'monthly',
            'grace_period_days' => 5,
            'late_fee_percentage' => 5,
            'status' => 'active',
            'terms_and_conditions' => fake()->paragraphs(2, true),
            'agreement_document_path' => null,
            'notes' => null,
        ];
    }

    /**
     * Terminated lease (historical record).
     */
    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terminated',
        ]);
    }
}
