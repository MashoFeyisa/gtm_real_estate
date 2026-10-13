<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FrontDeskShift extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
            'cash_collected' => 'float',
            'telebirr_collected' => 'float',
            'cbe_collected' => 'float',
            'card_pos_collected' => 'float',
            'total_collected' => 'float',
            'checkins_count' => 'integer',
            'checkouts_count' => 'integer',
            'finance_verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FrontDeskShift $shift) {
            if (empty($shift->shift_reference)) {
                $dateCode = Carbon::now()->format('Ymd');
                $randomSuffix = strtoupper(Str::random(4));
                $shift->shift_reference = "SFT-{$dateCode}-{$randomSuffix}";
            }
        });
    }

    public function receptionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receptionist_user_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function financeOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_officer_id');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'verified_by_finance' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'discrepancy_flagged' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'verified_by_finance' => 'Verified by Finance',
            'discrepancy_flagged' => 'Discrepancy Flagged',
            default => 'Pending Finance Audit',
        };
    }
}
