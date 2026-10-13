<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PropertyDocument>
 */
class PropertyDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            'title_deed_carta',
            'site_plan_mapa',
            'building_permit',
            'ownership_certificate',
            'tax_clearance',
            'seller_id',
        ]);
        $title = str_replace('_', ' ', $type);

        return [
            'property_id' => Property::factory(),
            'user_id' => User::factory(),
            'document_type' => $type,
            'title' => ucwords($title).' - '.fake()->lastName(),
            'document_number' => strtoupper(fake()->bothify('DOC-####-??')),
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
            'file_name' => Str::slug($title).'-document.pdf',
            'file_size' => fake()->numberBetween(120_000, 4_500_000),
            'mime_type' => 'application/pdf',
            'access_level' => fake()->randomElement(['confidential', 'verification_only', 'transaction_participants']),
            'status' => 'pending',
            'verification_notes' => null,
            'verified_at' => null,
            'verified_by' => null,
        ];
    }

    /**
     * Document verified by a verification officer.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'verified_at' => now(),
        ]);
    }

    /**
     * Document rejected during verification.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'verification_notes' => 'Illegible scan — please re-upload the original at higher resolution.',
        ]);
    }

    /**
     * Confidential title deed (highest trust-sensitivity per SRS §28).
     */
    public function titleDeed(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'title_deed_carta',
            'title' => 'Title Deed (የይዞታ ካርታ) - '.fake()->lastName(),
            'access_level' => 'confidential',
        ]);
    }
}
