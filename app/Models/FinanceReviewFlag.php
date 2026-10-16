<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A durable "finance must look at this" flag (rollback safety).
 *
 * A refund or a rejected payment normally puts the offer, reservation or rent
 * schedule back to the state the money set. When that entity has already moved
 * on (an offer was countered or accepted, a reservation was confirmed), forcing
 * the old state would destroy a later fact, so the money event records a flag
 * instead and the finance desk decides what to do.
 */
class FinanceReviewFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'payment_id',
        'reason',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }
}
