<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PropertyViewing extends Model
{
    use HasFactory;

    protected $fillable = [
        'viewing_reference',
        'property_id',
        'agent_id',
        'user_id',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'viewing_date',
        'viewing_time',
        'viewing_type',
        'status',
        'buyer_notes',
        'agent_notes',
    ];

    protected $casts = [
        'viewing_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (PropertyViewing $viewing) {
            if (empty($viewing->viewing_reference)) {
                $dateCode = Carbon::now()->format('Ym');
                $randomSuffix = strtoupper(Str::random(4));
                $viewing->viewing_reference = "VIEW-AA-{$dateCode}-{$randomSuffix}";
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['requested', 'confirmed', 'rescheduled']);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function getFormattedTimeSlotAttribute(): string
    {
        return match ($this->viewing_time) {
            'morning_09_11' => 'Morning (9:00 AM - 11:30 AM)',
            'afternoon_14_16' => 'Afternoon (2:00 PM - 4:30 PM)',
            'evening_17_19' => 'Evening (5:00 PM - 6:30 PM)',
            default => str_replace('_', ' ', $this->viewing_time),
        };
    }
}
