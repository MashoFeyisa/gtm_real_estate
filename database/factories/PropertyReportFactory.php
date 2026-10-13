<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyReport>
 */
class PropertyReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => fake()->boolean(80) ? Property::factory() : null,
            'user_id' => fake()->boolean(70) ? User::factory() : null,
            'subject' => fake()->randomElement([
                'Suspicious listing photos',
                'Price does not match advert',
                'Agent asked for off-platform payment',
                'Duplicate listing detected',
            ]),
            'reason' => fake()->randomElement([
                'scam',
                'fake_property',
                'duplicate',
                'wrong_price',
                'misleading',
                'agent_misconduct',
                'service_complaint',
            ]),
            'description' => fake()->paragraphs(2, true),
            'contact_email' => fake()->safeEmail(),
            'status' => 'pending',
            'resolved_by' => null,
            'resolution_notes' => null,
        ];
    }

    /**
     * Report currently being investigated by the moderation desk.
     */
    public function underInvestigation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'under_investigation',
        ]);
    }

    /**
     * Report resolved by a moderator.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolution_notes' => 'Listing suspended pending owner re-verification.',
        ]);
    }
}
