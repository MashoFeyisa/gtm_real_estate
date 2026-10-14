<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ProfessionalServiceRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'appointment_date' => 'datetime',
        'fee' => 'decimal:2',
        'client_rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professional_user_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getQuoteAmountAttribute(): ?float
    {
        return $this->fee !== null ? (float) $this->fee : null;
    }

    public function getScheduledAtAttribute(): ?Carbon
    {
        return $this->appointment_date;
    }
}
