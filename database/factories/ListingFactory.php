<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $listingType = fake()->randomElement(['sale', 'rent', 'stay']);

        return [
            'property_id' => Property::factory(),
            'listing_type' => $listingType,
            'title' => ucwords(fake()->words(4, true)),
            'description' => fake()->paragraph(),
            'house_rules' => null,
            'price' => $listingType === 'stay'
                ? fake()->randomElement([1800, 3200, 4500])
                : fake()->randomElement([2500000, 4500000, 35000, 60000]),
            'currency' => 'ETB',
            'pricing_period' => match ($listingType) {
                'rent' => 'monthly',
                'stay' => 'nightly',
                'lease' => 'yearly',
                default => 'total',
            },
            'deposit_amount' => null,
            'cleaning_fee' => $listingType === 'stay' ? fake()->randomElement([400, 600, 850]) : null,
            'service_fee' => null,
            'minimum_stay' => $listingType === 'stay' ? 1 : null,
            'maximum_stay' => $listingType === 'stay' ? 30 : null,
            'available_from' => now()->toDateString(),
            'available_to' => now()->addMonths(6)->toDateString(),
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now(),
        ];
    }

    /**
     * Listing awaiting moderator review.
     */
    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_review',
            'published_at' => null,
        ]);
    }

    /**
     * Short-stay listing.
     */
    public function stay(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => 'stay',
            'pricing_period' => 'nightly',
            'price' => fake()->randomElement([1800, 3200, 4500, 6800]),
            'cleaning_fee' => fake()->randomElement([400, 600, 850]),
            'minimum_stay' => 1,
            'maximum_stay' => 30,
        ]);
    }
}
