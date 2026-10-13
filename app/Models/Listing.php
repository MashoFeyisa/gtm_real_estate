<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Listing extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'deposit_amount' => 'float',
            'cleaning_fee' => 'float',
            'service_fee' => 'float',
            'minimum_stay' => 'integer',
            'maximum_stay' => 'integer',
            'quality_score' => 'integer',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Listing $listing) {
            if (empty($listing->listing_code)) {
                $typePrefix = strtoupper(substr($listing->listing_type ?: 'SALE', 0, 4));
                do {
                    $randomSuffix = strtoupper(Str::random(6));
                    $candidateCode = 'LST-'.$typePrefix.'-'.date('Ymd').'-'.$randomSuffix;
                } while (static::query()->where('listing_code', $candidateCode)->exists());

                $listing->listing_code = $candidateCode;
            }

            if (empty($listing->published_at) && $listing->status === 'published') {
                $listing->published_at = now();
            }
        });

        static::saving(function (Listing $listing) {
            if ($listing->property) {
                $prop = $listing->property;
                $score = 0;
                if ($prop->images()->count() >= 1) {
                    $score += 25;
                }
                if ($prop->images()->count() >= 3) {
                    $score += 15;
                }
                if (strlen((string) $prop->description) > 80) {
                    $score += 20;
                }
                if ($prop->documents()->count() >= 1) {
                    $score += 25;
                }
                if ($listing->price > 0 && ! empty($prop->city)) {
                    $score += 15;
                }
                $listing->quality_score = min(100, $score);
            }
        });

        static::updated(function (Listing $listing) {
            if ($listing->wasChanged('price') && $listing->getOriginal('price') !== null) {
                $oldPrice = (float) $listing->getOriginal('price');
                $newPrice = (float) $listing->price;
                if ($oldPrice !== $newPrice && $listing->property_id) {
                    ListingPriceHistory::create([
                        'listing_id' => $listing->id,
                        'property_id' => $listing->property_id,
                        'old_price' => $oldPrice,
                        'new_price' => $newPrice,
                        'currency' => $listing->currency ?: 'ETB',
                        'changed_by_user_id' => auth()->id(),
                        'reason' => 'Listing price update',
                    ]);

                    AuditLog::log(
                        action: 'listing.price_updated',
                        entityType: 'Listing',
                        entityId: $listing->id,
                        oldValue: ['price' => $oldPrice],
                        newValue: ['price' => $newPrice],
                        user: auth()->user(),
                    );
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(ListingPriceHistory::class)->latest();
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('status', ['published', 'active']);
    }

    public function scopeForSale(Builder $query): Builder
    {
        return $query->where('listing_type', 'sale');
    }

    public function scopeForRent(Builder $query): Builder
    {
        return $query->where('listing_type', 'rent');
    }

    public function scopeForStay(Builder $query): Builder
    {
        return $query->where('listing_type', 'stay');
    }

    public function scopeForLease(Builder $query): Builder
    {
        return $query->where('listing_type', 'lease');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        return ($this->currency ?: 'ETB').' '.number_format((float) $this->price);
    }

    public function getPriceSuffixAttribute(): string
    {
        return match ($this->pricing_period) {
            'monthly' => '/ month',
            'nightly' => '/ night',
            'yearly' => '/ year',
            default => '',
        };
    }

    public function getIsShortStayAttribute(): bool
    {
        return $this->listing_type === 'stay' || $this->pricing_period === 'nightly';
    }
}
