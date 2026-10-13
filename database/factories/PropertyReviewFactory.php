<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyReview>
 */
class PropertyReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory()->forStay(),
            'user_id' => User::factory(),
            'booking_id' => fake()->boolean(60) ? Booking::factory()->completed() : null,
            'rating' => fake()->numberBetween(3, 5),
            'cleanliness_rating' => fake()->numberBetween(3, 5),
            'accuracy_rating' => fake()->numberBetween(3, 5),
            'communication_rating' => fake()->numberBetween(3, 5),
            'location_rating' => fake()->numberBetween(3, 5),
            'value_rating' => fake()->numberBetween(3, 5),
            'review_title' => fake()->sentence(4),
            'review_body' => fake()->paragraphs(2, true),
            'host_reply' => null,
            'host_replied_at' => null,
            'is_verified_stay' => true,
            'status' => 'published',
        ];
    }

    /**
     * Review awaiting moderation approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Review with a host reply attached.
     */
    public function withHostReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'host_reply' => 'Thank you for staying with us! We hope to welcome you back soon.',
            'host_replied_at' => now()->subDays(fake()->numberBetween(1, 14)),
        ]);
    }
}
