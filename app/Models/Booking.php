<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'total_nights' => 'integer',
            'guests_count' => 'integer',
            'nightly_price' => 'float',
            'accommodation_total' => 'float',
            'cleaning_fee' => 'float',
            'service_fee' => 'float',
            'security_deposit' => 'float',
            'total_amount' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_reference)) {
                $areaCode = 'AA';
                $dateCode = Carbon::now()->format('Ym');
                $randomSuffix = strtoupper(Str::random(4));
                $booking->booking_reference = "STAY-{$areaCode}-{$dateCode}-{$randomSuffix}";
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guestServiceRequests(): HasMany
    {
        return $this->hasMany(GuestServiceRequest::class)->latest();
    }

    /**
     * Payments submitted against this reservation, newest first.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable')->latest();
    }

    /**
     * Most recent payment attempt, reusing the eager-loaded collection when present.
     */
    public function latestPayment(): ?Payment
    {
        if ($this->relationLoaded('payments')) {
            return $this->payments->sortByDesc('id')->first();
        }

        return $this->payments()->first();
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function awaitsPaymentConfirmation(): bool
    {
        return $this->payment_status === 'awaiting_confirmation';
    }

    /**
     * Confirm the reservation once its payment has been verified (SRS §78–§83).
     */
    public function markPaid(): void
    {
        $this->update([
            'status' => $this->status === 'pending' ? 'confirmed' : $this->status,
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Flag a bank transfer / deposit slip as submitted and awaiting finance audit.
     */
    public function markAwaitingPaymentConfirmation(): void
    {
        $this->update(['payment_status' => 'awaiting_confirmation']);
    }

    /**
     * Allow the guest to retry after the finance desk rejected a payment.
     */
    public function markPaymentRejected(): void
    {
        $this->update(['payment_status' => 'rejected']);
    }

    public function getPaymentStatusBadgeAttribute(): array
    {
        return match ($this->payment_status) {
            'paid' => ['Payment Confirmed', 'bg-emerald-100 text-emerald-800 border-emerald-300'],
            'awaiting_confirmation' => ['Awaiting Finance Confirmation', 'bg-amber-100 text-amber-800 border-amber-300'],
            'rejected' => ['Payment Rejected', 'bg-rose-100 text-rose-800 border-rose-300'],
            default => ['Payment Due', 'bg-slate-100 text-slate-700 border-slate-300'],
        };
    }

    public static function completeExpiredReservations(): int
    {
        return static::query()
            ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereDate('check_out_date', '<=', now()->toDateString())
            ->update(['status' => 'completed']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['cancelled', 'declined', 'checked_out', 'completed']);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereIn('status', ['confirmed', 'checked_in', 'completed']);
    }

    /**
     * Scope to find bookings overlapping a given date range to prevent double-booking.
     */
    public function scopeOverlapping(Builder $query, string $checkIn, string $checkOut): Builder
    {
        $checkInDate = Carbon::parse($checkIn)->format('Y-m-d');
        $checkOutDate = Carbon::parse($checkOut)->format('Y-m-d');

        return $query->active()
            ->whereDate('check_in_date', '<', $checkOutDate)
            ->whereDate('check_out_date', '>', $checkInDate);
    }

    public function getFormattedTotalAttribute(): string
    {
        return ($this->currency ?? 'ETB').' '.number_format((float) $this->total_amount);
    }
}
