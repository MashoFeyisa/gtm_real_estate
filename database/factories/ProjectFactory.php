<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucwords(fake()->words(3, true)).' Residences';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.strtolower(Str::random(5)),
            'developer_name' => ucwords(fake()->company()),
            'developer_verified' => fake()->boolean(40),
            'developer_phone' => '+2519'.fake()->numerify('########'),
            'developer_email' => fake()->safeEmail(),
            'agent_id' => null,
            'city' => fake()->randomElement(['Addis Ababa', 'Hawassa', 'Dire Dawa', 'Adama']),
            'subcity' => fake()->randomElement(['Bole', 'Yeka', 'Kirkos']),
            'address' => fake()->streetAddress(),
            'latitude' => 9.0 + fake()->randomFloat(4, -0.05, 0.05),
            'longitude' => 38.75 + fake()->randomFloat(4, -0.05, 0.05),
            'project_type' => fake()->randomElement(['residential', 'mixed_use', 'commercial']),
            'construction_status' => fake()->randomElement(['planning', 'under_construction', 'near_completion']),
            'overall_completion_percentage' => fake()->numberBetween(5, 95),
            'expected_completion_date' => 'Q'.fake()->numberBetween(1, 4).' '.now()->addYear()->year,
            'land_area_sqm' => fake()->randomFloat(2, 500, 8000),
            'total_buildings_count' => fake()->numberBetween(1, 4),
            'total_units_count' => fake()->numberBetween(6, 60),
            'starting_price' => fake()->randomElement([3500000, 7500000, 12500000]),
            'currency' => 'ETB',
            'description' => fake()->paragraphs(2, true),
            'facilities' => [
                '3-Phase Power & Backup Generator',
                '24/7 Gated Security & CCTV',
                'Underground Parking',
            ],
            'featured_image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80',
            'gallery' => [],
            'is_featured' => false,
            'status' => 'published',
        ];
    }
}
