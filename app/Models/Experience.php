<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Experience extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'host_user_id',
        'title',
        'slug',
        'city',
        'location_address',
        'category',
        'description',
        'price_etb',
        'price_usd',
        'duration_hours',
        'max_participants',
        'languages',
        'included_items',
        'meeting_point',
        'latitude',
        'longitude',
        'cover_image',
        'status',
        'is_featured',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_etb' => 'float',
            'price_usd' => 'float',
            'duration_hours' => 'float',
            'max_participants' => 'integer',
            'languages' => 'array',
            'included_items' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Experience $experience) {
            if (empty($experience->slug)) {
                $baseSlug = Str::slug($experience->title);
                $experience->slug = $baseSlug.'-'.Str::lower(Str::random(5));
            }
        });
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ExperienceBooking::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
