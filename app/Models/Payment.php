<?php

namespace App\Models;

use App\Services\FinanceSettings;
use App\Services\LedgerService;
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
        'idempotency_key',
        'idempotency_hash',
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
        'platform_fee_rate',
        'tax_amount',
        'vat_rate',
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
        'platform_fee_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'vat_rate' => 'decimal:4',
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

            // Freeze the rate that produced this payment's money (SRS §171).
            // A later rate change must never recalculate this record.
            $settings = app(FinanceSettings::class);

            if ($payment->platform_fee_rate === null) {
                $payment->platform_fee_rate = $settings->platformFeeRate();
            }

            if ($payment->vat_rate === null) {
                $payment->vat_rate = $settings->vatRate();
            }

            if (empty($payment->net_amount) && isset($payment->amount)) {
                $payment->net_amount = $settings->netAmount(
                    (float) $payment->amount,
                    (float) ($payment->platform_fee ?? 0),
                    (float) ($payment->tax_amount ?? 0),
                );
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

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
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
     * Record the auditable double-entry ledger entries for a cleared
     * transaction and advance the payable entity it settles (SRS §82).
     *
     * The journal always balances: the cash received is split into the amount
     * owed to the beneficiary (gross less the platform service fee and the VAT
     * charged on top of it), the platform service fee itself, and the VAT
     * payable the platform owes the tax authority.
     */
    public function settle(bool $syncPayable = true): void
    {
        $revenueCategory = match ($this->payment_type) {
            'stay_booking', 'booking_deposit' => 'booking_revenue',
            'unit_reservation' => 'developer_deposit',
            'rent_installment' => 'rental_revenue',
            default => 'earnest_deposit',
        };

        $amount = (float) $this->amount;
        $fee = (float) $this->platform_fee;
        $vat = (float) $this->tax_amount;
        $net = app(FinanceSettings::class)->netAmount($amount, $fee, $vat);

        $common = [
            'payment_id' => $this->id,
            'currency' => $this->currency,
            'reference' => $this->bank_transaction_reference,
            'user_id' => $this->user_id,
        ];

        $legs = [
            $common + [
                'entry_type' => 'debit',
                'category' => 'cash',
                'amount' => $amount,
                'description' => "Cash received for {$this->transaction_number} ({$this->type_label})",
            ],
            $common + [
                'entry_type' => 'credit',
                'category' => $revenueCategory,
                'amount' => $net,
                'description' => "Net credit for {$this->transaction_number} ({$this->type_label})",
            ],
        ];

        if ($fee > 0) {
            $legs[] = $common + [
                'entry_type' => 'credit',
                'category' => 'platform_fee',
                'amount' => $fee,
                'rate_snapshot' => ['key' => FinanceRate::PLATFORM_FEE, 'rate' => (float) $this->platform_fee_rate],
                'description' => "Platform service fee for {$this->transaction_number}",
            ];
        }

        if ($vat > 0) {
            $legs[] = $common + [
                'entry_type' => 'credit',
                'category' => 'vat_payable',
                'amount' => $vat,
                'rate_snapshot' => ['key' => FinanceRate::VAT, 'rate' => (float) $this->vat_rate],
                'description' => "VAT on the platform service fee for {$this->transaction_number}",
            ];
        }

        app(LedgerService::class)->post('payment:'.$this->id, $legs);

        if ($syncPayable) {
            $this->syncPayableAfterSettlement();
        }
    }

    /**
     * Reverse or flag the payable entity after a refund (SRS §132).
     *
     * A refund undoes whatever the settled payment had advanced, but only while
     * the payable is still in the exact state the payment set. If it has since
     * moved on (an offer was countered or accepted, a reservation confirmed, a
     * rent schedule re-let), forcing the old state would destroy a later
     * business fact, so the refund records a flag for the finance desk instead.
     */
    public function reversePayableAfterRefund(bool $isFullRefund = true): void
    {
        if (! $this->payable_type || ! $this->payable_id) {
            return;
        }

        if ($this->payableIs(Booking::class)) {
            $booking = Booking::find($this->payable_id);

            if (! $booking) {
                return;
            }

            if ($booking->status === 'completed') {
                $this->flagForReview($booking, "Refund on a booking already {$booking->status}.");

                return;
            }

            $booking->update([
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'refund_amount' => (float) $booking->refund_amount + (float) $this->refunded_amount,
                'refund_status' => $isFullRefund ? 'refunded' : 'pending_disbursement',
            ]);

            return;
        }

        if ($this->payableIs(PropertyOffer::class)) {
            $offer = PropertyOffer::find($this->payable_id);

            if (! $offer) {
                return;
            }

            if ($offer->status !== 'under_review') {
                $this->flagForReview($offer, "Refund on an offer that is {$offer->status}, not the under_review state the payment set.");

                return;
            }

            $offer->update(['status' => $isFullRefund ? 'rejected' : 'under_review']);

            return;
        }

        if ($this->payableIs(UnitReservation::class)) {
            $reservation = UnitReservation::find($this->payable_id);

            if (! $reservation) {
                return;
            }

            if ($reservation->status !== 'reserved') {
                $this->flagForReview($reservation, "Refund on a reservation that is {$reservation->status}, not the reserved state the payment set.");

                return;
            }

            $reservation->update(['status' => $isFullRefund ? 'cancelled' : 'reserved']);

            return;
        }

        if ($this->payableIs(RentSchedule::class)) {
            $schedule = RentSchedule::find($this->payable_id);

            if (! $schedule) {
                return;
            }

            if ($schedule->status !== 'paid') {
                $this->flagForReview($schedule, "Refund on a rent schedule that is {$schedule->status}, not the paid state the payment set.");

                return;
            }

            $schedule->update([
                'status' => $isFullRefund ? 'pending' : 'partial',
                'amount_paid' => 0,
                'paid_at' => null,
                'payment_id' => null,
            ]);
        }
    }

    /**
     * Flag the payable entity after a rejected transaction so the payer may retry.
     *
     * A failed payment must not leave an offer, reservation or rent schedule
     * looking as though it had been paid, so each payable is put back to the
     * state it held before the money was submitted - but only while it is still
     * in the state this payment set. Anything that has moved on is flagged for
     * the finance desk rather than silently rewritten.
     */
    public function flagPayableRejected(): void
    {
        if (! $this->payable_type || ! $this->payable_id) {
            return;
        }

        if ($this->payableIs(Booking::class)) {
            Booking::find($this->payable_id)?->markPaymentRejected();

            return;
        }

        if ($this->payableIs(PropertyOffer::class)) {
            $offer = PropertyOffer::find($this->payable_id);

            if ($offer && $offer->status !== 'under_review') {
                $this->flagForReview($offer, "Rejected payment on an offer that is {$offer->status}, not the under_review state the payment set.");

                return;
            }

            PropertyOffer::where('id', $this->payable_id)
                ->whereIn('status', ['under_review'])
                ->update(['status' => 'submitted']);

            return;
        }

        if ($this->payableIs(UnitReservation::class)) {
            $reservation = UnitReservation::find($this->payable_id);

            if ($reservation && $reservation->status !== 'reserved') {
                $this->flagForReview($reservation, "Rejected payment on a reservation that is {$reservation->status}, not the reserved state the payment set.");

                return;
            }

            UnitReservation::where('id', $this->payable_id)
                ->whereIn('status', ['reserved'])
                ->update(['status' => 'pending']);

            return;
        }

        if ($this->payableIs(RentSchedule::class)) {
            $schedule = RentSchedule::find($this->payable_id);

            if ($schedule && $schedule->status !== 'paid') {
                $this->flagForReview($schedule, "Rejected payment on a rent schedule that is {$schedule->status}, not the paid state the payment set.");

                return;
            }

            RentSchedule::where('id', $this->payable_id)->update([
                'status' => 'pending',
                'amount_paid' => 0,
                'paid_at' => null,
                'payment_id' => null,
            ]);
        }
    }

    /**
     * Record a durable flag that a money event could not safely roll back its
     * payable because the payable had already moved on.
     */
    protected function flagForReview(Model $reviewable, string $reason): void
    {
        FinanceReviewFlag::create([
            'reviewable_type' => $reviewable::class,
            'reviewable_id' => $reviewable->getKey(),
            'payment_id' => $this->id,
            'reason' => $reason,
        ]);
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
