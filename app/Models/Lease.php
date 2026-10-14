<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lease extends Model
{
    use HasFactory;

    protected $fillable = [
        'lease_code',
        'property_id',
        'agent_id',
        'landlord_user_id',
        'tenant_user_id',
        'tenant_name',
        'tenant_email',
        'tenant_phone',
        'tenant_id_type',
        'tenant_id_number',
        'tenant_occupation',
        'emergency_contact_name',
        'emergency_contact_phone',
        'start_date',
        'end_date',
        'duration_months',
        'monthly_rent',
        'security_deposit',
        'deposit_status',
        'deposit_deductions',
        'deposit_refunded',
        'deposit_deduction_reason',
        'deposit_returned_at',
        'payment_frequency',
        'grace_period_days',
        'late_fee_percentage',
        'status',
        'signed_at',
        'tenant_signature',
        'terms_and_conditions',
        'agreement_document_path',
        'notes',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'deposit_returned_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_months' => 'integer',
        'monthly_rent' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'deposit_deductions' => 'decimal:2',
        'deposit_refunded' => 'decimal:2',
        'late_fee_percentage' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Lease $lease) {
            if (empty($lease->lease_code)) {
                $year = date('Y');
                $random = strtoupper(substr(uniqid(), -6));
                $lease->lease_code = "LSE-ETH-{$year}-{$random}";
            }
        });

        static::updated(function (Lease $lease) {
            if ($lease->wasChanged('status') && $lease->status === 'terminated') {
                $lease->removeUnpaidSchedules();
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

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_user_id');
    }

    public function tenantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->tenantUser();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(RentSchedule::class)->orderBy('installment_number');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Generate installment rent schedule according to frequency and duration.
     */
    public function generateSchedules(): void
    {
        // Remove existing schedules if any
        $this->schedules()->delete();

        $ethiopianMonths = [
            1 => 'Meskerem (መስከረም)',
            2 => 'Tikimt (ጥቅምት)',
            3 => 'Hidar (ኅዳር)',
            4 => 'Tahsas (ታኅሣሥ)',
            5 => 'Tir (ጥር)',
            6 => 'Yakatit (የካቲት)',
            7 => 'Megabit (መጋቢት)',
            8 => 'Miazia (ሚያዝያ)',
            9 => 'Ginbot (ግንቦት)',
            10 => 'Sene (ሰኔ)',
            11 => 'Hamle (ሐምሌ)',
            12 => 'Nehase (ነሐሴ)',
        ];

        $intervalMonths = match ($this->payment_frequency) {
            'quarterly' => 3,
            'semi_annually' => 6,
            'annually' => 12,
            default => 1,
        };

        $totalInstallments = max(1, (int) ceil($this->duration_months / $intervalMonths));
        $installmentAmount = (float) $this->monthly_rent * $intervalMonths;

        $currentDate = Carbon::parse($this->start_date);

        for ($i = 1; $i <= $totalInstallments; $i++) {
            $dueDate = $currentDate->copy();

            // Map rough Ethiopian calendar month approximation
            $gregMonth = $dueDate->month;
            $ethMonthIndex = (($gregMonth + 3) % 12) ?: 12;
            $ethMonthName = $ethiopianMonths[$ethMonthIndex] ?? 'Month';

            $periodLabel = "Installment #{$i}: ".$dueDate->format('M Y')." ({$ethMonthName})";

            RentSchedule::create([
                'lease_id' => $this->id,
                'installment_number' => $i,
                'period_label' => $periodLabel,
                'due_date' => $dueDate->format('Y-m-d'),
                'amount_due' => $installmentAmount,
                'amount_paid' => 0.00,
                'status' => 'pending',
            ]);

            $currentDate->addMonths($intervalMonths);
        }
    }

    /**
     * Remove the outstanding rent installments of a terminated tenancy.
     *
     * No further rent can be collected once a lease is terminated, so the
     * unpaid installments are dropped from the schedule. Installments already
     * marked paid are retained as the auditable record of collected rent, and
     * the payments and financial ledger entries behind them are never touched.
     *
     * @return int Number of installments removed.
     */
    public function removeUnpaidSchedules(): int
    {
        return $this->schedules()
            ->where('status', '!=', 'paid')
            ->delete();
    }

    public function getTotalRentDueAttribute(): float
    {
        return (float) $this->schedules->sum('amount_due');
    }

    public function getTotalRentPaidAttribute(): float
    {
        return (float) $this->schedules->sum('amount_paid');
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return max(0, $this->total_rent_due - $this->total_rent_paid);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'active' => ['Active Lease', 'bg-emerald-100 text-emerald-800 border-emerald-300'],
            'draft' => ['Draft / Pending', 'bg-slate-100 text-slate-700 border-slate-300'],
            'pending_renewal' => ['Renewal Pending', 'bg-amber-100 text-amber-800 border-amber-300'],
            'expired' => ['Expired', 'bg-rose-100 text-rose-800 border-rose-300'],
            'terminated' => ['Terminated', 'bg-gray-100 text-gray-700 border-gray-300'],
            default => [ucfirst($this->status), 'bg-blue-100 text-blue-800 border-blue-300'],
        };
    }
}
