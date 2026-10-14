<?php

namespace App\Models;

use App\Services\EthiopianGeoService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'featured' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'float',
        'building_area' => 'integer',
        'floors' => 'integer',
        'parking_spaces' => 'integer',
        'balconies' => 'integer',
        'year_built' => 'integer',
        'has_electricity' => 'boolean',
        'has_water' => 'boolean',
        'has_generator' => 'boolean',
        'has_water_tank' => 'boolean',
        'has_solar' => 'boolean',
        'has_security' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'nearby_amenities' => 'array',
        'walk_score' => 'integer',
        'transit_score' => 'integer',
        'is_available' => 'boolean',
        'available_from' => 'date',
        'available_to' => 'date',
        'cleaning_fee' => 'float',
        'service_fee' => 'float',
        'weekend_price' => 'float',
        'seasonal_price' => 'float',
        'minimum_stay' => 'integer',
        'instant_booking_enabled' => 'boolean',
        'verification_level' => 'integer',
        'verified_badges' => 'array',
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Property $property) {
            if (empty($property->property_code)) {
                $property->property_code = $property->generatePropertyCode();
            }
        });

        static::saved(function (Property $property) {
            // Auto-sync primary commercial listing if no listings exist
            if ($property->listings()->count() === 0 && ! empty($property->price)) {
                $cat = strtolower((string) ($property->property_category ?? $property->category ?? ''));
                $isShortStayCategory = in_array($cat, ['guest_house', 'hotel', 'guesthouse', 'hotels'], true);

                $listingType = match (true) {
                    $property->type === 'stay' || ($isShortStayCategory && $property->type !== 'sale') => 'stay',
                    in_array(strtolower((string) $property->type), ['rent', 'lease'], true) => strtolower((string) $property->type),
                    default => 'sale',
                };

                $pricingPeriod = match ($listingType) {
                    'rent' => 'monthly',
                    'stay' => 'nightly',
                    'lease' => 'yearly',
                    default => 'total',
                };

                $property->listings()->create([
                    'user_id' => $property->user_id ?? $property->host_id ?? auth()->id(),
                    'listing_type' => $listingType,
                    'title' => $property->title,
                    'description' => $property->description,
                    'price' => $property->price,
                    'currency' => 'ETB',
                    'pricing_period' => $pricingPeriod,
                    'cleaning_fee' => (float) ($property->cleaning_fee ?? 0),
                    'service_fee' => ! is_null($property->service_fee) ? (float) $property->service_fee : 0,
                    'house_rules' => $property->house_rules,
                    'available_from' => $property->available_from,
                    'available_to' => $property->available_to,
                    'status' => in_array($property->status, ['draft', 'archived']) ? 'draft' : 'published',
                    'is_featured' => (bool) $property->featured,
                ]);
            }
        });
    }

    public function generatePropertyCode(): string
    {
        $cityCode = 'AA';
        $areaCode = 'CENT';

        $text = strtolower(implode(' ', array_filter([$this->address, $this->city])));
        if (str_contains($text, 'bole')) {
            $areaCode = 'BOLE';
        } elseif (str_contains($text, 'yeka')) {
            $areaCode = 'YEKA';
        } elseif (str_contains($text, 'kirkos') || str_contains($text, 'kazanchis')) {
            $areaCode = 'KRKS';
        } elseif (str_contains($text, 'harar')) {
            $cityCode = 'HR';
            $areaCode = 'HRAR';
        } elseif (str_contains($text, 'jijiga')) {
            $cityCode = 'SM';
            $areaCode = 'JJGA';
        } elseif (str_contains($text, 'dire dawa')) {
            $cityCode = 'DD';
            $areaCode = 'DIRD';
        }

        $cat = match (strtolower((string) ($this->property_category ?? $this->category ?? 'home'))) {
            'apartment' => 'APT',
            'villa' => 'VIL',
            'guest_house', 'guesthouse', 'guest house' => 'GHS',
            'condominium', 'condo', 'condos' => 'CON',
            'hotel', 'hotels' => 'HTL',
            'office', 'offices' => 'OFF',
            'land' => 'LND',
            'commercial' => 'COM',
            default => 'RES',
        };

        do {
            $seq = (string) ($this->id ? str_pad((string) $this->id, 8, '0', STR_PAD_LEFT) : random_int(10000000, 99999999));
            $code = "ETH-{$cityCode}-{$areaCode}-{$cat}-{$seq}";
        } while (static::query()->where('property_code', $code)->where('id', '!=', $this->id ?? 0)->exists());

        return $code;
    }

    /**
     * Restrict a query to properties that may be shown on the public site.
     */
    public function scopePublicVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNotIn('status', ['draft', 'archived']);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function activeListings(): HasMany
    {
        return $this->hasMany(Listing::class)->whereIn('status', ['published', 'active']);
    }

    public function saleListing(): ?Listing
    {
        return $this->listings()->where('listing_type', 'sale')->latest()->first();
    }

    public function rentListing(): ?Listing
    {
        return $this->listings()->where('listing_type', 'rent')->latest()->first();
    }

    public function stayListing(): ?Listing
    {
        return $this->listings()->where('listing_type', 'stay')->latest()->first();
    }

    public function isShortStay(): bool
    {
        $cat = strtolower((string) ($this->property_category ?? $this->category ?? ''));
        $shortStayCategories = ['guest_house', 'hotel', 'guesthouse', 'hotels', 'serviced_apartment'];

        return in_array($cat, $shortStayCategories, true)
            || strtolower((string) $this->type) === 'stay'
            || $this->stayListing() !== null;
    }

    public function getNightlyRate(): float
    {
        $stayListing = $this->stayListing();
        if ($stayListing && (float) $stayListing->price > 0) {
            return (float) $stayListing->price;
        }

        if (strtolower((string) $this->type) === 'rent') {
            // Divide monthly rent by 30 days
            $rate = round(((float) $this->price) / 30, 2);
        } else {
            // Total sale value 0.35%
            $rate = round(((float) $this->price) * 0.0035, 2);
        }

        return $rate > 0 ? $rate : 4500.00;
    }

    public function getCleaningFee(): float
    {
        $stayListing = $this->stayListing();
        if ($stayListing && ! is_null($stayListing->cleaning_fee)) {
            return (float) $stayListing->cleaning_fee;
        }

        if (! is_null($this->cleaning_fee)) {
            return (float) $this->cleaning_fee;
        }

        return 850.00;
    }

    /**
     * Platform service fee:
     * If specified as a flat amount on listing or property, use it.
     * Otherwise return null so that the dynamic 5% of accommodation subtotal applies.
     */
    public function getCustomServiceFee(): ?float
    {
        $stayListing = $this->stayListing();
        if ($stayListing && ! is_null($stayListing->service_fee) && (float) $stayListing->service_fee > 0) {
            return (float) $stayListing->service_fee;
        }

        if (! is_null($this->service_fee) && (float) $this->service_fee > 0) {
            return (float) $this->service_fee;
        }

        return null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(ListingPriceHistory::class)->latest();
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)->latest();
    }

    public function viewings(): HasMany
    {
        return $this->hasMany(PropertyViewing::class)->latest();
    }

    public function offers(): HasMany
    {
        return $this->hasMany(PropertyOffer::class)->latest();
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class)->latest();
    }

    public function parentBuilding(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'parent_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Property::class, 'parent_id')->latest();
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(PropertyInspection::class)->latest();
    }

    public function isAvailableForDates(string $checkIn, string $checkOut): bool
    {
        if ($this->is_available === false) {
            return false;
        }

        $cIn = Carbon::parse($checkIn)->startOfDay();
        $cOut = Carbon::parse($checkOut)->startOfDay();

        if ($this->available_from && $cIn->lt(Carbon::parse($this->available_from)->startOfDay())) {
            return false;
        }

        if ($this->available_to && $cOut->gt(Carbon::parse($this->available_to)->endOfDay())) {
            return false;
        }

        return ! $this->bookings()->overlapping($checkIn, $checkOut)->exists();
    }

    public function createListing(array $attributes): Listing
    {
        return $this->listings()->create(array_merge([
            'title' => $this->title,
            'description' => $this->description,
            'currency' => 'ETB',
            'status' => 'published',
        ], $attributes));
    }

    public function getCategoryAttribute(): ?string
    {
        return $this->attributes['property_category'] ?? $this->attributes['category'] ?? null;
    }

    public function setCategoryAttribute($value): void
    {
        $this->attributes['property_category'] = $value;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function frontDeskShifts(): HasMany
    {
        return $this->hasMany(FrontDeskShift::class)->latest();
    }

    public function guestServiceRequests(): HasMany
    {
        return $this->hasMany(GuestServiceRequest::class)->latest();
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PropertyDocument::class)->latest();
    }

    public function verifiedDocuments(): HasMany
    {
        return $this->hasMany(PropertyDocument::class)->where('status', 'verified')->latest();
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getVerificationLevelLabelAttribute(): string
    {
        return match ((int) $this->verification_level) {
            1 => 'Level 1 — Identity Verified',
            2 => 'Level 2 — Agent Licensed',
            3 => 'Level 3 — Documents Submitted',
            4 => 'Level 4 — Professionally Reviewed',
            5 => 'Level 5 — Transaction Ready',
            default => 'Level 0 — Unverified',
        };
    }

    public function hasVerificationBadge(string $badge): bool
    {
        $badges = (array) ($this->verified_badges ?? []);

        return in_array($badge, $badges, true);
    }

    /**
     * Get the default luxury background image path for fallback when no custom photos exist.
     */
    public function getDefaultBackgroundImageAttribute(): string
    {
        return match (strtolower((string) ($this->category ?? $this->property_category ?? ''))) {
            'apartment', 'condominium', 'condo', 'condos' => 'images/luxury/luxury-towers.jpg',
            'villa' => 'images/luxury/hero-skyline.jpg',
            'home', 'guest_house', 'guesthouse', 'guest house' => 'images/luxury/panoramic-park.jpg',
            'hotel', 'hotels' => 'images/luxury/central-plaza.jpg',
            'office', 'offices' => 'images/luxury/retail-boulevard.jpg',
            default => 'images/luxury/hero-skyline.jpg',
        };
    }

    /**
     * Resolve any image path into a publicly accessible asset URL.
     */
    public function resolveImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }

    /**
     * Get all image URLs for this property (gallery images with fallback to primary image or default luxury background).
     *
     * @return array<int, string>
     */
    public function getGalleryImagesAttribute(): array
    {
        $loadedImages = $this->relationLoaded('images') ? $this->images : $this->images()->get();
        $urls = $loadedImages->pluck('image_url')->filter()->values()->all();

        if (empty($urls) && $this->image_path) {
            $urls[] = $this->resolveImageUrl($this->image_path);
        }

        if (empty($urls)) {
            $urls[] = asset($this->default_background_image);
        }

        return $urls;
    }

    /**
     * Get the primary image URL (explicit image_path, first gallery image, or default luxury background).
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return $this->resolveImageUrl($this->image_path);
        }

        $firstImage = $this->relationLoaded('images') ? $this->images->first() : $this->images()->first();
        if ($firstImage?->image_url) {
            return $firstImage->image_url;
        }

        return asset($this->default_background_image);
    }

    /**
     * Get latitude and longitude coordinates for map plotting.
     *
     * @return array{0: float, 1: float}
     */
    public function getCoordinatesAttribute(): array
    {
        if (! empty($this->attributes['latitude']) && ! empty($this->attributes['longitude'])) {
            return [(float) $this->attributes['latitude'], (float) $this->attributes['longitude']];
        }

        $searchCorpus = strtolower(implode(' ', array_filter([
            $this->subcity,
            $this->neighborhood,
            $this->woreda,
            $this->address,
            $this->city,
            $this->title,
            $this->description,
        ])));

        $neighborhoods = [
            'bole' => [9.0016, 38.7844],
            'kazanchis' => [9.0205, 38.7675],
            'old airport' => [8.9882, 38.7364],
            'sarbet' => [8.9950, 38.7420],
            'cmc' => [9.0250, 38.8350],
            'summit' => [9.0175, 38.8550],
            'piassa' => [9.0350, 38.7520],
            'lebu' => [8.9550, 38.7200],
            'ayat' => [9.0300, 38.8700],
            'meganagna' => [9.0200, 38.8020],
            'gotera' => [8.9920, 38.7600],
            'gerji' => [8.9990, 38.8100],
            'saris' => [8.9500, 38.7650],
            'mexico' => [9.0120, 38.7450],
            'yeka' => [9.0320, 38.8150],
            'kirkos' => [9.0120, 38.7620],
            'lideta' => [9.0100, 38.7400],
            'arada' => [9.0350, 38.7520],
            'gulele' => [9.0650, 38.7400],
            'kolfe' => [9.0200, 38.7100],
            'akaky' => [8.9200, 38.7600],
            'lemi kura' => [9.0280, 38.8500],
        ];

        $propertyId = (int) ($this->id ?: 1);

        foreach ($neighborhoods as $name => $coords) {
            if (str_contains($searchCorpus, $name)) {
                $offsetLat = (($propertyId * 17) % 20 - 10) * 0.0008;
                $offsetLng = (($propertyId * 23) % 20 - 10) * 0.0008;

                return [round($coords[0] + $offsetLat, 6), round($coords[1] + $offsetLng, 6)];
            }
        }

        $baseLat = 9.0192;
        $baseLng = 38.7525;
        $offsetLat = (($propertyId * 29) % 40 - 20) * 0.0012;
        $offsetLng = (($propertyId * 37) % 40 - 20) * 0.0012;

        return [round($baseLat + $offsetLat, 6), round($baseLng + $offsetLng, 6)];
    }

    /**
     * Compact payload for interactive Leaflet map rendering.
     *
     * @return array<string, mixed>
     */
    public function getMapDataAttribute(): array
    {
        $coords = $this->coordinates;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'price' => (float) $this->price,
            'type' => $this->type,
            'category' => ucfirst($this->property_category ?? $this->category ?? 'Property'),
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'area' => $this->area,
            'city' => $this->city ?? 'Addis Ababa',
            'subcity' => $this->subcity,
            'woreda' => $this->woreda,
            'neighborhood' => $this->neighborhood,
            'address' => $this->subcity ? "{$this->subcity}, {$this->city}" : ($this->address ?? 'Prime Location'),
            'image' => $this->primary_image_url,
            'url' => route('properties.show', $this->slug),
            'lat' => $coords[0],
            'lng' => $coords[1],
            'walk_score' => $this->effective_walk_score,
            'transit_score' => $this->effective_transit_score,
        ];
    }

    /**
     * Nearby amenity radar items computed dynamically or retrieved from custom override.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getNearbyAmenitiesListAttribute(): array
    {
        if (! empty($this->nearby_amenities) && is_array($this->nearby_amenities)) {
            return $this->nearby_amenities;
        }

        $coords = $this->coordinates;

        return app(EthiopianGeoService::class)->calculateNearbyAmenities(
            $coords[0],
            $coords[1]
        );
    }

    /**
     * Effective walkability score (0-100).
     */
    public function getEffectiveWalkScoreAttribute(): int
    {
        if ($this->walk_score !== null) {
            return (int) $this->walk_score;
        }

        $coords = $this->coordinates;

        return app(EthiopianGeoService::class)->calculateWalkScore(
            $coords[0],
            $coords[1]
        );
    }

    /**
     * Effective public transit accessibility score (0-100).
     */
    public function getEffectiveTransitScoreAttribute(): int
    {
        if ($this->transit_score !== null) {
            return (int) $this->transit_score;
        }

        $coords = $this->coordinates;

        return app(EthiopianGeoService::class)->calculateTransitScore(
            $coords[0],
            $coords[1]
        );
    }

    /**
     * Qualitative badge for walk score.
     */
    public function getWalkScoreBadgeAttribute(): array
    {
        return app(EthiopianGeoService::class)->getScoreBadge(
            $this->effective_walk_score
        );
    }

    /**
     * Qualitative badge for transit score.
     */
    public function getTransitScoreBadgeAttribute(): array
    {
        return app(EthiopianGeoService::class)->getScoreBadge(
            $this->effective_transit_score
        );
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PropertyReview::class)->latest();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PropertyReport::class)->latest();
    }

    public function maintenanceTickets(): HasMany
    {
        return $this->hasMany(MaintenanceTicket::class)->latest();
    }

    public function getPricePerSqmAttribute(): ?float
    {
        $area = (float) ($this->building_area ?: $this->area);
        if ($area <= 0 || ! $this->price) {
            return null;
        }

        return round((float) $this->price / $area, 2);
    }

    public function getAverageRatingAttribute(): float
    {
        $avg = $this->reviews()->where('status', 'published')->avg('rating');

        return $avg ? round((float) $avg, 1) : 5.0;
    }

    public function getReviewsCountAttribute(): int
    {
        return (int) $this->reviews()->where('status', 'published')->count();
    }

    public function getQualityScoreAttribute(): array
    {
        $points = 0;
        $breakdown = [];

        // 1. Photos (20 pts)
        $imageCount = $this->images()->count() + ($this->image_path ? 1 : 0);
        if ($imageCount >= 5) {
            $points += 20;
            $breakdown['photos'] = ['earned' => 20, 'max' => 20, 'status' => 'pass', 'label' => 'Comprehensive photo gallery (5+ photos)'];
        } elseif ($imageCount >= 1) {
            $points += 10;
            $breakdown['photos'] = ['earned' => 10, 'max' => 20, 'status' => 'warning', 'label' => 'Add at least 5 photos for maximum visibility'];
        } else {
            $breakdown['photos'] = ['earned' => 0, 'max' => 20, 'status' => 'fail', 'label' => 'No photos uploaded'];
        }

        // 2. Detailed Description (15 pts)
        $descLength = mb_strlen(trim((string) $this->description));
        if ($descLength >= 150) {
            $points += 15;
            $breakdown['description'] = ['earned' => 15, 'max' => 15, 'status' => 'pass', 'label' => 'Detailed, professional property narrative'];
        } elseif ($descLength > 20) {
            $points += 8;
            $breakdown['description'] = ['earned' => 8, 'max' => 15, 'status' => 'warning', 'label' => 'Expand description to at least 150 characters'];
        } else {
            $breakdown['description'] = ['earned' => 0, 'max' => 15, 'status' => 'fail', 'label' => 'Description is missing or too short'];
        }

        // 3. GIS Coordinates & Accuracy (15 pts)
        if ($this->latitude && $this->longitude) {
            $points += 15;
            $breakdown['gis'] = ['earned' => 15, 'max' => 15, 'status' => 'pass', 'label' => 'Precise GPS map pin configured'];
        } else {
            $breakdown['gis'] = ['earned' => 0, 'max' => 15, 'status' => 'fail', 'label' => 'Drop a GPS pin on the map to unlock area search'];
        }

        // 4. Pricing & Transaction Terms (15 pts)
        if ($this->price > 0 && in_array($this->type, ['sale', 'rent', 'stay'], true)) {
            $points += 15;
            $breakdown['pricing'] = ['earned' => 15, 'max' => 15, 'status' => 'pass', 'label' => 'Price and transaction type clearly defined'];
        } else {
            $breakdown['pricing'] = ['earned' => 0, 'max' => 15, 'status' => 'fail', 'label' => 'Set explicit pricing and deal terms'];
        }

        // 5. Physical Specifications (15 pts)
        $specs = 0;
        if ($this->bedrooms > 0) {
            $specs++;
        }
        if ($this->bathrooms > 0) {
            $specs++;
        }
        if ($this->area > 0) {
            $specs++;
        }
        if ($this->building_area > 0) {
            $specs++;
        }
        if ($specs >= 3) {
            $points += 15;
            $breakdown['specs'] = ['earned' => 15, 'max' => 15, 'status' => 'pass', 'label' => 'Complete room counts and square footage'];
        } else {
            $points += 7;
            $breakdown['specs'] = ['earned' => 7, 'max' => 15, 'status' => 'warning', 'label' => 'Provide bedrooms, bathrooms, and land/building area'];
        }

        // 6. Infrastructure & Utilities Readiness (10 pts)
        $utilityCount = collect([$this->has_electricity, $this->has_water, $this->has_generator, $this->has_water_tank, $this->has_solar, $this->has_security])->filter()->count();
        if ($utilityCount >= 2) {
            $points += 10;
            $breakdown['utilities'] = ['earned' => 10, 'max' => 10, 'status' => 'pass', 'label' => 'Infrastructure & utility reliability documented'];
        } else {
            $breakdown['utilities'] = ['earned' => 0, 'max' => 10, 'status' => 'warning', 'label' => 'Verify water, power backup, or security features'];
        }

        // 7. Verified Documentation (10 pts)
        $hasDocs = $this->documents()->whereIn('status', ['verified', 'submitted'])->exists();
        if ($hasDocs) {
            $points += 10;
            $breakdown['documents'] = ['earned' => 10, 'max' => 10, 'status' => 'pass', 'label' => 'Legal title/ownership document attached'];
        } else {
            $breakdown['documents'] = ['earned' => 0, 'max' => 10, 'status' => 'neutral', 'label' => 'Attach title or deed for Level 3 verification'];
        }

        $color = match (true) {
            $points >= 85 => 'emerald',
            $points >= 65 => 'teal',
            $points >= 45 => 'amber',
            default => 'rose',
        };

        return [
            'score' => $points,
            'badge' => $points.'%',
            'color' => $color,
            'breakdown' => $breakdown,
        ];
    }

    public function getInvestmentMetricsAttribute(): array
    {
        $price = (float) $this->price;
        $monthlyRent = $this->type === 'rent' ? $price : round($price * 0.005, 0);
        $annualGrossRent = $monthlyRent * 12;
        $grossYield = $price > 0 ? round(($annualGrossRent / $price) * 100, 2) : 0;
        $netOperatingIncome = round($annualGrossRent * 0.85, 0);
        $netYield = $price > 0 ? round(($netOperatingIncome / $price) * 100, 2) : 0;

        return [
            'monthly_rent_estimate' => $monthlyRent,
            'annual_gross_income' => $annualGrossRent,
            'gross_yield_percentage' => $grossYield,
            'net_operating_income' => $netOperatingIncome,
            'net_yield_percentage' => $netYield,
        ];
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class)->latest();
    }
}
