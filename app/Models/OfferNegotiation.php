<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferNegotiation extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_offer_id',
        'sender_type',
        'amount',
        'currency',
        'message',
        'terms',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'terms' => 'array',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(PropertyOffer::class, 'property_offer_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return ($this->currency ?? 'ETB').' '.number_format((float) $this->amount);
    }
}
