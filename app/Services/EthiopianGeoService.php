<?php

namespace App\Services;

use App\Models\SubcityBoundary;
use Illuminate\Database\Eloquent\Collection;

class EthiopianGeoService
{
    /**
     * Official Addis Ababa Sub-Cities (ክፍለ ከተሞች) and Regional Metropolitan Hubs.
     *
     * @var array<string, array{name: string, amharic: string, lat: float, lng: float}>
     */
    protected const SUBCITIES = [
        'Bole' => ['name' => 'Bole', 'amharic' => 'ቦሌ', 'lat' => 9.0016, 'lng' => 38.7844],
        'Kirkos' => ['name' => 'Kirkos', 'amharic' => 'ቂርቆስ', 'lat' => 9.0120, 'lng' => 38.7620],
        'Yeka' => ['name' => 'Yeka', 'amharic' => 'የካ', 'lat' => 9.0320, 'lng' => 38.8150],
        'Nifas Silk-Lafto' => ['name' => 'Nifas Silk-Lafto', 'amharic' => 'ንፋስ ስልክ ላፍቶ', 'lat' => 8.9750, 'lng' => 38.7300],
        'Lideta' => ['name' => 'Lideta', 'amharic' => 'ልደታ', 'lat' => 9.0100, 'lng' => 38.7400],
        'Arada' => ['name' => 'Arada', 'amharic' => 'አራዳ', 'lat' => 9.0350, 'lng' => 38.7520],
        'Addis Ketema' => ['name' => 'Addis Ketema', 'amharic' => 'አዲስ ከተማ', 'lat' => 9.0300, 'lng' => 38.7350],
        'Gulele' => ['name' => 'Gulele', 'amharic' => 'ጉለሌ', 'lat' => 9.0650, 'lng' => 38.7400],
        'Kolfe Keranio' => ['name' => 'Kolfe Keranio', 'amharic' => 'ኮልፌ ቀራኒዮ', 'lat' => 9.0200, 'lng' => 38.7100],
        'Akaky Kaliti' => ['name' => 'Akaky Kaliti', 'amharic' => 'አቃቂ ቃሊቲ', 'lat' => 8.9200, 'lng' => 38.7600],
        'Lemi Kura' => ['name' => 'Lemi Kura', 'amharic' => 'ለሚ ኩራ', 'lat' => 9.0280, 'lng' => 38.8500],
    ];

    /**
     * Searchable Ethiopian Cities requested for property discovery.
     *
     * @var array<string, array{key: string, name: string, amharic: string, lat: float, lng: float, aliases: array<int, string>}>
     */
    public const CITIES = [
        'Addis Ababa' => [
            'key' => 'Addis Ababa',
            'name' => 'Addis Ababa',
            'amharic' => 'አዲስ አበባ',
            'lat' => 9.0300,
            'lng' => 38.7400,
            'aliases' => ['Addis Ababa', 'Addis Abeba', 'Finfinnee', 'Sheger'],
        ],
        'Harar' => [
            'key' => 'Harar',
            'name' => 'Harar',
            'amharic' => 'ሐረር',
            'lat' => 9.3139,
            'lng' => 42.1182,
            'aliases' => ['Harar', 'ethiopia Harar', 'Harari'],
        ],
        'Jijjiga' => [
            'key' => 'Jijjiga',
            'name' => 'Jijjiga',
            'amharic' => 'ጅጅጋ',
            'lat' => 9.3500,
            'lng' => 42.8000,
            'aliases' => ['Jijjiga', 'Jigjiga'],
        ],
        'Shashamane' => [
            'key' => 'Shashamane',
            'name' => 'Shashamane',
            'amharic' => 'ሻሸመኔ',
            'lat' => 7.2000,
            'lng' => 38.6000,
            'aliases' => ['Shashamane', 'shashamane', 'Shashemene'],
        ],
        'Dire Dawa' => [
            'key' => 'Dire Dawa',
            'name' => 'Dire Dawa',
            'amharic' => 'ድሬዳዋ',
            'lat' => 9.6009,
            'lng' => 41.8501,
            'aliases' => ['Dire Dawa', 'DireeDawa', 'Dire Dawa', 'Diredawa'],
        ],
        'Adama' => [
            'key' => 'Adama',
            'name' => 'Adama',
            'amharic' => 'አዳማ',
            'lat' => 8.5400,
            'lng' => 39.2700,
            'aliases' => ['Adama', 'Nazret', 'Adama (Nazret)'],
        ],
        'Hawassa' => [
            'key' => 'Hawassa',
            'name' => 'Hawassa',
            'amharic' => 'ሀዋሳ',
            'lat' => 7.0504,
            'lng' => 38.4955,
            'aliases' => ['Hawassa', 'Awassa'],
        ],
    ];

    /**
     * Regional Major Cities in Ethiopia.
     *
     * @var array<string, array{name: string, amharic: string, lat: float, lng: float}>
     */
    protected const REGIONAL_CITIES = [
        'Hawassa' => ['name' => 'Hawassa', 'amharic' => 'ሀዋሳ', 'lat' => 7.0504, 'lng' => 38.4955],
        'Bahir Dar' => ['name' => 'Bahir Dar', 'amharic' => 'ባህር ዳር', 'lat' => 11.5742, 'lng' => 37.3614],
        'Adama' => ['name' => 'Adama (Nazret)', 'amharic' => 'አዳማ', 'lat' => 8.5400, 'lng' => 39.2700],
        'Bishoftu' => ['name' => 'Bishoftu', 'amharic' => 'ቢሾፍቱ', 'lat' => 8.7523, 'lng' => 38.9785],
        'Dire Dawa' => ['name' => 'Dire Dawa', 'amharic' => 'ድሬዳዋ', 'lat' => 9.6009, 'lng' => 41.8501],
    ];

    /**
     * Curated Verified Landmark Points of Interest (POIs) across Addis Ababa & environs.
     *
     * @var array<int, array<string, mixed>>
     */
    protected const POINTS_OF_INTEREST = [
        // Education & Academies
        [
            'name' => 'International Community School (ICS Addis)',
            'amharic' => 'አይ ሲ ኤስ ዓለም አቀፍ ትምህርት ቤት',
            'category' => 'education',
            'icon' => '🎓',
            'lat' => 8.9815,
            'lng' => 38.7410,
        ],
        [
            'name' => 'Bingham Academy (Sarbet)',
            'amharic' => 'ቢንግሃም አካዳሚ',
            'category' => 'education',
            'icon' => '🎓',
            'lat' => 8.9950,
            'lng' => 38.7420,
        ],
        [
            'name' => 'Sandford International School',
            'amharic' => 'ሳንድፎርድ ዓለም አቀፍ ትምህርት ቤት',
            'category' => 'education',
            'icon' => '🎓',
            'lat' => 9.0350,
            'lng' => 38.7800,
        ],
        [
            'name' => 'Lycée Guébré-Mariam',
            'amharic' => 'ሊሴ ገብረማርያም የፈረንሳይ ትምህርት ቤት',
            'category' => 'education',
            'icon' => '🎓',
            'lat' => 9.0180,
            'lng' => 38.7560,
        ],
        [
            'name' => 'Addis Ababa University (Sidist Kilo Main Campus)',
            'amharic' => 'አዲስ አበባ ዩኒቨርሲቲ',
            'category' => 'education',
            'icon' => '🏛️',
            'lat' => 9.0475,
            'lng' => 38.7610,
        ],

        // Healthcare & Hospitals
        [
            'name' => 'Korean Hospital (MCM General Hospital - Gerji)',
            'amharic' => 'የኮሪያ ሆስፒታል (ኤም ሲ ኤም)',
            'category' => 'health',
            'icon' => '🏥',
            'lat' => 9.0062,
            'lng' => 38.8155,
        ],
        [
            'name' => 'Nordic Medical Centre (Bole)',
            'amharic' => 'ኖርዲክ ሜዲካል ሴንተር',
            'category' => 'health',
            'icon' => '🏥',
            'lat' => 8.9920,
            'lng' => 38.7840,
        ],
        [
            'name' => 'Tikur Anbessa (Black Lion) Specialized Hospital',
            'amharic' => 'ጥቁር አንበሳ ስፔሻላይዝድ ሆስፒታል',
            'category' => 'health',
            'icon' => '🏥',
            'lat' => 9.0195,
            'lng' => 38.7510,
        ],
        [
            'name' => 'St. Paul’s Hospital Millennium Medical College',
            'amharic' => 'ቅዱስ ጳውሎስ ሆስፒታል',
            'category' => 'health',
            'icon' => '🏥',
            'lat' => 9.0585,
            'lng' => 38.7230,
        ],
        [
            'name' => 'Kadisco General Hospital (Gerji)',
            'amharic' => 'ካዲሲኮ አጠቃላይ ሆስፒታል',
            'category' => 'health',
            'icon' => '🏥',
            'lat' => 8.9910,
            'lng' => 38.8020,
        ],

        // Airports & Transit Stations
        [
            'name' => 'Bole International Airport (ADD Terminal 1 & 2)',
            'amharic' => 'ቦሌ ዓለም አቀፍ አውሮፕላን ማረፊያ',
            'category' => 'transit',
            'icon' => '✈️',
            'lat' => 8.9778,
            'lng' => 38.7993,
        ],
        [
            'name' => 'Light Rail Transit (Stadium Central Station)',
            'amharic' => 'ቀላል ባቡር ስታዲየም ጣቢያ',
            'category' => 'transit',
            'icon' => '🚊',
            'lat' => 9.0125,
            'lng' => 38.7565,
        ],
        [
            'name' => 'Light Rail Transit (CMC Terminal Station)',
            'amharic' => 'ቀላል ባቡር ሲ ኤም ሲ ጣቢያ',
            'category' => 'transit',
            'icon' => '🚊',
            'lat' => 9.0225,
            'lng' => 38.8350,
        ],
        [
            'name' => 'Light Rail Transit (Ayat Terminal Station)',
            'amharic' => 'ቀላል ባቡር አያት መነሻ ጣቢያ',
            'category' => 'transit',
            'icon' => '🚊',
            'lat' => 9.0260,
            'lng' => 38.8710,
        ],
        [
            'name' => 'Light Rail Transit (Tor Hailoch Interchange)',
            'amharic' => 'ቀላል ባቡር ጦር ኃይሎች ጣቢያ',
            'category' => 'transit',
            'icon' => '🚊',
            'lat' => 9.0110,
            'lng' => 38.7250,
        ],

        // Diplomatic & International Governance
        [
            'name' => 'African Union (AU) Headquarters',
            'amharic' => 'የአፍሪካ ህብረት ዋና መስሪያ ቤት',
            'category' => 'diplomatic',
            'icon' => '🌍',
            'lat' => 9.0016,
            'lng' => 38.7455,
        ],
        [
            'name' => 'United Nations Economic Commission for Africa (UNECA)',
            'amharic' => 'የተባበሩት መንግስታት ኢኮኖሚክ ኮሚሽን (ኢካ)',
            'category' => 'diplomatic',
            'icon' => '🏛️',
            'lat' => 9.0163,
            'lng' => 38.7652,
        ],
        [
            'name' => 'Embassy of the United States of America',
            'amharic' => 'የአሜሪካ ኤምባሲ',
            'category' => 'diplomatic',
            'icon' => '🏛️',
            'lat' => 9.0620,
            'lng' => 38.7820,
        ],

        // Shopping & Commercial Hubs
        [
            'name' => 'Edna Mall & Medhanialem Commercial District',
            'amharic' => 'ኤድና ሞል እና መድኃኔዓለም የገበያ ማዕከል',
            'category' => 'shopping',
            'icon' => '🛍️',
            'lat' => 8.9985,
            'lng' => 38.7865,
        ],
        [
            'name' => 'Century Mall (Gurd Shola)',
            'amharic' => 'ሴንቸሪ ሞል ጉርድ ሾላ',
            'category' => 'shopping',
            'icon' => '🛍️',
            'lat' => 9.0195,
            'lng' => 38.8120,
        ],
        [
            'name' => 'Gast Mall (CMC Michael)',
            'amharic' => 'ጋስት ሞል ሲ ኤም ሲ',
            'category' => 'shopping',
            'icon' => '🛍️',
            'lat' => 9.0215,
            'lng' => 38.8415,
        ],
        [
            'name' => 'Friendship City Center (Bole Road)',
            'amharic' => 'ፍሬንድሺፕ የገበያ ማዕከል',
            'category' => 'shopping',
            'icon' => '🛍️',
            'lat' => 8.9930,
            'lng' => 38.7870,
        ],

        // Lifestyle, Parks & Culture
        [
            'name' => 'Unity Park (Grand Palace)',
            'amharic' => 'አንድነት ፓርክ',
            'category' => 'lifestyle',
            'icon' => '🌳',
            'lat' => 9.0255,
            'lng' => 38.7630,
        ],
        [
            'name' => 'Entoto Natural Park & Viewpoint',
            'amharic' => 'እንጦጦ ፓርክ',
            'category' => 'lifestyle',
            'icon' => '🌲',
            'lat' => 9.0920,
            'lng' => 38.7610,
        ],
        [
            'name' => 'Friendship Square & Science Museum',
            'amharic' => 'የወዳጅነት አደባባይ እና የሳይንስ ሙዚየም',
            'category' => 'lifestyle',
            'icon' => '⛲',
            'lat' => 9.0170,
            'lng' => 38.7610,
        ],
    ];

    /**
     * Compute distance in kilometers using the Haversine Great-Circle formula.
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }

    /**
     * Calculate and return nearby verified amenities grouped by category.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function calculateNearbyAmenities(float $lat, float $lng, float $radiusKm = 12.0, int $limitPerCategory = 3): array
    {
        $grouped = [
            'education' => [],
            'health' => [],
            'transit' => [],
            'shopping' => [],
            'diplomatic' => [],
            'lifestyle' => [],
        ];

        foreach (self::POINTS_OF_INTEREST as $poi) {
            $dist = $this->haversineDistance($lat, $lng, $poi['lat'], $poi['lng']);

            if ($dist <= $radiusKm) {
                $walkMinutes = max(1, (int) round($dist * 12.5)); // ~4.8 km/h
                $driveMinutes = max(2, (int) round($dist * 2.5)); // ~24 km/h city speed

                $category = $poi['category'];
                if (isset($grouped[$category])) {
                    $grouped[$category][] = array_merge($poi, [
                        'distance_km' => $dist,
                        'walk_minutes' => $walkMinutes,
                        'drive_minutes' => $driveMinutes,
                        'formatted_distance' => $dist < 1 ? ((int) round($dist * 1000)).' m' : number_format($dist, 1).' km',
                    ]);
                }
            }
        }

        // Sort each category by proximity and take limit
        foreach ($grouped as $cat => &$items) {
            usort($items, fn ($a, $b) => $a['distance_km'] <=> $b['distance_km']);
            $items = array_slice($items, 0, $limitPerCategory);
        }

        return array_filter($grouped, fn ($items) => count($items) > 0);
    }

    /**
     * Compute a walkability score from 0 to 100 based on amenity proximity.
     */
    public function calculateWalkScore(float $lat, float $lng): int
    {
        $amenities = $this->calculateNearbyAmenities($lat, $lng, radiusKm: 3.5, limitPerCategory: 5);

        $score = 40; // baseline for urban Addis Ababa

        foreach ($amenities as $category => $items) {
            foreach ($items as $item) {
                if ($item['distance_km'] <= 0.8) {
                    $score += 8;
                } elseif ($item['distance_km'] <= 1.5) {
                    $score += 4;
                } elseif ($item['distance_km'] <= 2.5) {
                    $score += 2;
                }
            }
        }

        return min(100, max(20, $score));
    }

    /**
     * Compute public transit & mobility score from 0 to 100.
     */
    public function calculateTransitScore(float $lat, float $lng): int
    {
        $amenities = $this->calculateNearbyAmenities($lat, $lng, radiusKm: 6.0, limitPerCategory: 5);
        $transitItems = $amenities['transit'] ?? [];

        if (empty($transitItems)) {
            return 35;
        }

        $closestDist = $transitItems[0]['distance_km'] ?? 5.0;

        if ($closestDist <= 0.8) {
            return 95;
        }

        if ($closestDist <= 1.5) {
            return 85;
        }

        if ($closestDist <= 3.0) {
            return 72;
        }

        if ($closestDist <= 5.0) {
            return 58;
        }

        return 42;
    }

    /**
     * Get qualitative assessment label and color badge for a score.
     *
     * @return array{label: string, amharic: string, badge_class: string}
     */
    public function getScoreBadge(int $score): array
    {
        if ($score >= 85) {
            return [
                'label' => 'Walker’s Paradise',
                'amharic' => 'እጅግ ተስማሚና ቅርብ አካባቢ',
                'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            ];
        }

        if ($score >= 70) {
            return [
                'label' => 'Very Walkable',
                'amharic' => 'በእግር ለመጓዝ ምቹ',
                'badge_class' => 'bg-teal-100 text-teal-800 border-teal-300',
            ];
        }

        if ($score >= 50) {
            return [
                'label' => 'Somewhat Walkable',
                'amharic' => 'መጠነኛ የእግር ጉዞ ምቹ',
                'badge_class' => 'bg-amber-100 text-amber-800 border-amber-300',
            ];
        }

        return [
            'label' => 'Car-Dependent',
            'amharic' => 'የመኪና ጉዞ የሚጠይቅ',
            'badge_class' => 'bg-slate-100 text-slate-800 border-slate-300',
        ];
    }

    /**
     * Get list of searchable Ethiopian cities.
     *
     * @return array<string, array{key: string, name: string, amharic: string, lat: float, lng: float, aliases: array<int, string>}>
     */
    public function getCities(): array
    {
        return self::CITIES;
    }

    /**
     * Get list of Addis Ababa subcities.
     *
     * @return array<string, array{name: string, amharic: string, lat: float, lng: float}>
     */
    public function getSubcities(): array
    {
        return self::SUBCITIES;
    }

    /**
     * Get list of major regional cities.
     *
     * @return array<string, array{name: string, amharic: string, lat: float, lng: float}>
     */
    public function getRegionalCities(): array
    {
        return self::REGIONAL_CITIES;
    }

    /**
     * Get all verified points of interest.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllPointsOfInterest(): array
    {
        return self::POINTS_OF_INTEREST;
    }

    /**
     * Retrieve subcity polygon boundaries from the database.
     *
     * @return Collection<int, SubcityBoundary>
     */
    public function getSubcityBoundaries(string $city = 'Addis Ababa')
    {
        return SubcityBoundary::query()->byCity($city)->get();
    }

    /**
     * Determine which subcity boundary contains the given latitude and longitude.
     */
    public function findSubcityByCoordinates(float $lat, float $lng, string $city = 'Addis Ababa'): ?SubcityBoundary
    {
        $boundaries = $this->getSubcityBoundaries($city);

        foreach ($boundaries as $boundary) {
            if ($boundary->containsPoint($lat, $lng)) {
                return $boundary;
            }
        }

        return null;
    }

    /**
     * Generate GeoJSON FeatureCollection of subcity polygon boundaries.
     *
     * @return array{type: string, features: array<int, array<string, mixed>>}
     */
    public function getGeoJsonFeatureCollection(string $city = 'Addis Ababa'): array
    {
        $boundaries = $this->getSubcityBoundaries($city);

        return [
            'type' => 'FeatureCollection',
            'features' => $boundaries->map(fn (SubcityBoundary $b) => $b->toGeoJsonFeature())->values()->all(),
        ];
    }
}
