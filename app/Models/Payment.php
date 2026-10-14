<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'payable_type',
        'payable_id',
        'property_id',
        'user_id',
        'payer_name',
        'payer_email',
        'payer_phone',
        'payment_type',
        'payment_method',
        'bank_name',
        'bank_account_number',
        'bank_transaction_reference',
        'deposit_slip_path',
        'currency',
        'amount',
        'platform_fee',
        'tax_amount',
        'net_amount',
        'status',
        'notes',
        'admin_notes',
        'verified_at',
        'verified_by',
        'payment_metadata',
        'refunded_amount',
        'refund_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'payment_metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->transaction_number)) {
                $year = date('Y');
                $random = strtoupper(substr(uniqid(), -6));
                $payment->transaction_number = "TXN-ETH-{$year}-{$random}";
            }

            if (empty($payment->net_amount) && isset($payment->amount)) {
                $payment->net_amount = max(0, (float) $payment->amount - (float) ($payment->platform_fee ?? 0) - (float) ($payment->tax_amount ?? 0));
            }
        });
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(FinancialLedger::class);
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'telebirr' => 'Telebirr (ቴሌብር)',
            'cbe_birr' => 'CBE Birr (ሲቢኢ ብር)',
            'chapa' => 'Chapa Pay',
            'bank_transfer' => 'Bank Transfer / Deposit Slip (ባንክ ክፍያ)',
            'cash' => 'Cash at Office (ጥሬ ገንዘብ)',
            default => ucfirst(str_replace('_', ' ', (string) $this->payment_method)),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->payment_type) {
            'earnest_deposit' => 'Earnest Deposit (የቅድመ ክፍያ/ማስያዣ)',
            'stay_booking' => 'Short-Stay Accommodation (የአዳር ማረፊያ ክፍያ)',
            'unit_reservation' => 'Off-Plan Unit Reservation (የቤት ምዝገባ ክፍያ)',
            'rent_installment' => 'Monthly Rent (የኪራይ ክፍያ)',
            'service_fee' => 'Service Fee (የአገልግሎት ክፍያ)',
            default => ucfirst(str_replace('_', ' ', (string) $this->payment_type)),
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'completed' => ['Verified / Completed', 'bg-emerald-100 text-emerald-800 border-emerald-300'],
            'pending' => ['Pending Review', 'bg-amber-100 text-amber-800 border-amber-300'],
            'failed' => ['Failed', 'bg-rose-100 text-rose-800 border-rose-300'],
            'refunded' => ['Refunded', 'bg-purple-100 text-purple-800 border-purple-300'],
            'cancelled' => ['Cancelled', 'bg-slate-100 text-slate-700 border-slate-300'],
            default => ['Processing', 'bg-blue-100 text-blue-800 border-blue-300'],
        };
    }

    public function getDepositSlipUrlAttribute(): ?string
    {
        if (! $this->deposit_slip_path) {
            return null;
        }

        return Storage::url($this->deposit_slip_path);
    }

    /**
     * Record the auditable ledger entries for a cleared transaction and advance
     * the payable entity it settles (SRS §82).
     */
    public function settle(): void
    {
        FinancialLedger::firstOrCreate([
            'payment_id' => $this->id,
            'category' => match ($this->payment_type) {
                'stay_booking' => 'booking_revenue',
                'unit_reservation' => 'developer_deposit',
                'rent_installment' => 'rental_revenue',
                default => 'earnest_deposit',
            },
        ], [
            'entry_type' => 'credit',
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => "Gross credit for {$this->transaction_number} ({$this->type_label})",
            'reference' => $this->bank_transaction_reference,
            'user_id' => $this->user_id,
        ]);

        if ($this->platform_fee > 0) {
            FinancialLedger::firstOrCreate([
                'payment_id' => $this->id,
                'category' => 'platform_fee',
            ], [
                'entry_type' => 'credit',
                'amount' => $this->platform_fee,
                'currency' => $this->currency,
                'description' => "Platform service fee for {$this->transaction_number}",
                'reference' => $this->bank_transaction_reference,
                'user_id' => $this->user_id,
            ]);
        }

        $this->syncPayableAfterSettlement();
    }

    /**
     * Flag the payable entity after a rejected transaction so the payer may retry.
     */
    public function flagPayableRejected(): void
    {
        if ($this->payableIs(Booking::class)) {
            Booking::find($this->payable_id)?->markPaymentRejected();
        }
    }

    /**
     * Advance the payable entity that this cleared transaction paid for.
     */
    protected function syncPayableAfterSettlement(): void
    {
        if (! $this->payable_type || ! $this->payable_id) {
            return;
        }

        if ($this->payableIs(Booking::class)) {
            $booking = Booking::find($this->payable_id);
            if ($booking && ! in_array($booking->status, ['cancelled', 'completed'], true)) {
                $booking->markPaid();
            }

            return;
        }

        if ($this->payableIs(PropertyOffer::class)) {
            PropertyOffer::where('id', $this->payable_id)->update(['status' => 'under_review']);

            return;
        }

        if ($this->payableIs(UnitReservation::class)) {
            UnitReservation::where('id', $this->payable_id)->update(['status' => 'reserved']);

            return;
        }

        if ($this->payableIs(RentSchedule::class)) {
            RentSchedule::where('id', $this->payable_id)->update([
                'status' => 'paid',
                'amount_paid' => $this->amount,
                'paid_at' => now(),
                'payment_id' => $this->id,
                'payment_method' => $this->payment_method,
            ]);
        }
    }

    protected function payableIs(string $model): bool
    {
        return $this->payable_type === $model
            || str_ends_with((string) $this->payable_type, class_basename($model));
    }
}
