<?php

namespace Database\Seeders;

use App\Models\ListingType;
use App\Models\PropertyCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Database-configurable property taxonomy from the platform specification:
 * property categories grouped by segment, and listing types mapped to the
 * five primary marketplace modes.
 */
class PropertyTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();
        $this->seedListingTypes();
    }

    /** @return array<string, array<int, string>> */
    private function categoryCatalog(): array
    {
        return [
            'Residential' => [
                'House',
                'Villa',
                'Apartment',
                'Condominium',
                'Townhouse',
                'Duplex',
                'Penthouse',
                'Studio',
                'Guesthouse',
                'Residential compound',
            ],
            'Commercial' => [
                'Office',
                'Shop',
                'Mall',
                'Hotel',
                'Restaurant',
                'Warehouse',
                'Commercial building',
                'Business center',
            ],
            'Industrial' => [
                'Factory',
                'Industrial compound',
                'Manufacturing facility',
                'Storage facility',
            ],
            'Hospitality' => [
                'Hotel',
                'Guesthouse',
                'Lodge',
                'Resort',
                'Serviced apartment',
                'Vacation villa',
                'Short-stay apartment',
            ],
            'Special' => [
                'Mixed-use',
                'Investment property',
                'Development project',
                'Event center',
            ],
        ];
    }

    /** @return array<string, array<int, string>> */
    private function listingTypeCatalog(): array
    {
        return [
            'buy' => ['For Sale', 'Auction', 'Investment Opportunity'],
            'rent' => ['For Rent'],
            'lease' => ['For Lease'],
            'stay' => ['Short Stay', 'Long Stay'],
            'new_projects' => ['New Development', 'Pre-Sale', 'Off-Plan', 'Joint Venture Opportunity'],
        ];
    }

    private function seedCategories(): void
    {
        $groupIcons = [
            'Residential' => '🏠',
            'Commercial' => '🏢',
            'Industrial' => '🏭',
            'Hospitality' => '🏨',
            'Special' => '⭐',
        ];

        $sortOrder = 0;
        foreach ($this->categoryCatalog() as $group => $names) {
            foreach ($names as $name) {
                $slug = Str::slug($name);

                PropertyCategory::query()->firstOrCreate(
                    ['group' => $group, 'slug' => $slug],
                    [
                        'name' => $name,
                        'icon' => $groupIcons[$group] ?? null,
                        'is_active' => true,
                        'sort_order' => $sortOrder++,
                    ]
                );
            }
        }
    }

    private function seedListingTypes(): void
    {
        $sortOrder = 0;
        foreach ($this->listingTypeCatalog() as $mode => $names) {
            foreach ($names as $name) {
                ListingType::query()->updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'mode' => $mode,
                        'name' => $name,
                        'is_active' => true,
                        'sort_order' => $sortOrder++,
                    ]
                );
            }
        }
    }
}
