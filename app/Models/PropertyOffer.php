<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PropertyOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_reference',
        'property_id',
        'agent_id',
        'user_id',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'offer_type',
        'original_price',
        'offered_price',
        'currency',
        'financing_type',
        'deposit_proposed',
        'valid_until',
        'status',
        'current_counter_amount',
        'latest_counter_by',
        'message',
        'supporting_document_path',
        'order_id',
        'lease_id',
        'closing_status',
        'closing_notes',
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'offered_price' => 'decimal:2',
        'current_counter_amount' => 'decimal:2',
        'deposit_proposed' => 'decimal:2',
        'valid_until' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PropertyOffer $offer) {
            if (empty($offer->offer_reference)) {
                $dateCode = Carbon::now()->format('Ym');
                $randomSuffix = strtoupper(Str::random(4));
                $offer->offer_reference = "OFF-ETH-AA-{$dateCode}-{$randomSuffix}";
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function negotiations(): HasMany
    {
        return $this->hasMany(OfferNegotiation::class)->orderBy('created_at', 'asc');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['submitted', 'under_review', 'countered']);
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', 'accepted');
    }

    public function getFormattedOriginalPriceAttribute(): string
    {
        return ($this->currency ?? 'ETB').' '.number_format((float) $this->original_price);
    }

    public function getFormattedOfferedPriceAttribute(): string
    {
        return ($this->currency ?? 'ETB').' '.number_format((float) $this->offered_price);
    }

    public function getFormattedCurrentAmountAttribute(): string
    {
        $amount = $this->current_counter_amount ?? $this->offered_price;

        return ($this->currency ?? 'ETB').' '.number_format((float) $amount);
    }

    public function getDifferencePercentageAttribute(): float
    {
        if ($this->original_price > 0) {
            $effective = (float) ($this->current_counter_amount ?? $this->offered_price);

            return round((($effective - (float) $this->original_price) / (float) $this->original_price) * 100, 1);
        }

        return 0;
    }
}
