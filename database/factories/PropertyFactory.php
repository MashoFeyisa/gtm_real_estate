<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucwords(fake()->words(3, true)).' Villa';

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->paragraphs(3, true),
            'house_rules' => null,
            'price' => fake()->randomElement([2500000, 4500000, 8500000, 12500000, 22000000]),
            'cleaning_fee' => 0,
            'service_fee' => null,
            'bedrooms' => fake()->numberBetween(1, 6),
            'bathrooms' => fake()->numberBetween(1, 5),
            'floors' => fake()->numberBetween(1, 4),
            'parking_spaces' => fake()->numberBetween(0, 3),
            'balconies' => fake()->numberBetween(0, 4),
            'year_built' => fake()->numberBetween(2005, 2025),
            'condition' => fake()->randomElement(['new', 'excellent', 'good', 'renovated']),
            'has_electricity' => true,
            'has_water' => true,
            'has_generator' => fake()->boolean(70),
            'has_water_tank' => true,
            'has_solar' => fake()->boolean(40),
            'has_security' => true,
            'area' => fake()->numberBetween(80, 600),
            'building_area' => fake()->numberBetween(60, 450),
            'type' => 'sale',
            'property_category' => 'villa',
            'status' => 'published',
            'verification_level' => 0,
            'verification_status' => 'unverified',
            'verified_badges' => [],
            'city' => 'Addis Ababa',
            'subcity' => fake()->randomElement(['Bole', 'Kirkos', 'Yeka', 'Lideta', 'Arada', 'Gullele']),
            'address' => fake()->streetAddress(),
            'latitude' => 9.0 + fake()->randomFloat(4, -0.05, 0.05),
            'longitude' => 38.75 + fake()->randomFloat(4, -0.05, 0.05),
            'featured' => false,
            'is_active' => true,
            'is_available' => true,
            'available_from' => now()->toDateString(),
            'available_to' => now()->addMonths(6)->toDateString(),
        ];
    }

    /**
     * Long-term rental listing (SRS: RENT marketplace mode).
     */
    public function forRent(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => ucwords(fake()->words(3, true)).' Apartment for Rent',
            'type' => 'rent',
            'property_category' => fake()->randomElement(['apartment', 'condominium', 'home']),
            'price' => fake()->randomElement([18000, 35000, 60000, 95000]),
        ]);
    }

    /**
     * Short-stay accommodation listing (SRS: STAY marketplace mode).
     */
    public function forStay(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => ucwords(fake()->words(3, true)).' Guest House Suite',
            'type' => 'stay',
            'property_category' => fake()->randomElement(['guest_house', 'serviced_apartment', 'hotel']),
            'price' => fake()->randomElement([1800, 3200, 4500, 6800]),
            'cleaning_fee' => fake()->randomElement([400, 600, 850]),
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'house_rules' => "1. Valid ID required at check-in.\n2. No indoor smoking.\n3. Quiet hours 22:00-07:00.",
        ]);
    }

    /**
     * Property carrying verified trust badges and a verification level.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_level' => 4,
            'verification_status' => 'verified',
            'verified_badges' => ['Verified Seller', 'Documents Submitted', 'Professionally Reviewed'],
            'verified_at' => now(),
        ]);
    }

    /**
     * Featured / promoted listing.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }
}
