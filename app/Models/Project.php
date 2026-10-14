<?php

namespace App\Models;

use App\Services\EthiopianGeoService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'name',
        'slug',
        'developer_name',
        'developer_user_id',
        'developer_phone',
        'developer_email',
        'developer_verified',
        'agent_id',
        'city',
        'subcity',
        'address',
        'latitude',
        'longitude',
        'project_type',
        'construction_status',
        'overall_completion_percentage',
        'expected_completion_date',
        'land_area_sqm',
        'total_buildings_count',
        'total_units_count',
        'starting_price',
        'currency',
        'description',
        'facilities',
        'featured_image',
        'gallery',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'facilities' => 'array',
        'gallery' => 'array',
        'developer_verified' => 'boolean',
        'is_featured' => 'boolean',
        'overall_completion_percentage' => 'integer',
        'starting_price' => 'decimal:2',
        'land_area_sqm' => 'decimal:2',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get latitude and longitude coordinates for map plotting.
     *
     * Falls back to the project's sub-city centroid, then to central Addis Ababa,
     * so a project registered without a picked pin still plots on the map.
     *
     * @return array{0: float, 1: float}
     */
    public function getCoordinatesAttribute(): array
    {
        if (! empty($this->attributes['latitude']) && ! empty($this->attributes['longitude'])) {
            return [(float) $this->attributes['latitude'], (float) $this->attributes['longitude']];
        }

        $subcities = app(EthiopianGeoService::class)->getSubcities();
        $needle = strtolower((string) ($this->subcity ?? $this->city));

        foreach ($subcities as $key => $subcity) {
            if ($needle !== '' && (str_contains(strtolower((string) $key), $needle) || str_contains($needle, strtolower((string) $key)))) {
                return [(float) $subcity['lat'], (float) $subcity['lng']];
            }
        }

        return [9.0192, 38.7525];
    }

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name).'-'.strtolower(Str::random(5));
            }

            if (empty($project->project_code)) {
                $city = strtolower($project->city ?? 'addis ababa');
                $cityCode = str_contains($city, 'addis') ? 'AA' : strtoupper(substr($project->city ?? 'ET', 0, 2));
                $year = Carbon::now()->format('Y');
                $randomSuffix = strtoupper(Str::random(4));
                $project->project_code = "ETH-DEV-{$cityCode}-{$year}-{$randomSuffix}";
            }
        });
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(ProjectBuilding::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProjectUnit::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ConstructionMilestone::class)->orderBy('order_index');
    }

    public function paymentPlans(): HasMany
    {
        return $this->hasMany(DeveloperPaymentPlan::class)->orderBy('order_index');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(UnitReservation::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'developer_user_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getAvailableUnitsCountAttribute(): int
    {
        return $this->units()->where('status', 'available')->count();
    }

    public function getReservedUnitsCountAttribute(): int
    {
        return $this->units()->where('status', 'reserved')->count();
    }

    public function getSoldUnitsCountAttribute(): int
    {
        return $this->units()->where('status', 'sold')->count();
    }

    public function getFormattedStartingPriceAttribute(): string
    {
        return ($this->currency ?? 'ETB').' '.number_format((float) $this->starting_price);
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        if (! empty($this->featured_image)) {
            if (str_starts_with($this->featured_image, 'http') || str_starts_with($this->featured_image, '/')) {
                return $this->featured_image;
            }

            return asset('storage/'.$this->featured_image);
        }

        return 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80';
    }
}
