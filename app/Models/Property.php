<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $guarded = [];

    protected $casts = [
        'featured' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'float',
    ];

    /**
     * Restrict a query to properties that may be shown on the public site.
     */
    public function scopePublicVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNotIn('status', ['draft', 'archived']);
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get the default luxury background image path for fallback when no custom photos exist.
     */
    public function getDefaultBackgroundImageAttribute(): string
    {
        return match (strtolower((string) ($this->category ?? $this->property_category ?? ''))) {
            'apartment' => 'images/luxury/luxury-towers.jpg',
            'villa' => 'images/luxury/hero-skyline.jpg',
            'home' => 'images/luxury/panoramic-park.jpg',
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
}
