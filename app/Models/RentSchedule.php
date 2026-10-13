<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'lease_id',
        'installment_number',
        'period_label',
        'due_date',
        'amount_due',
        'amount_paid',
        'status',
        'payment_id',
        'paid_at',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'paid') {
            return false;
        }

        return $this->due_date && Carbon::parse($this->due_date)->startOfDay()->isPast();
    }

    public function getStatusBadgeAttribute(): array
    {
        if ($this->status === 'paid') {
            return ['Paid', 'bg-emerald-100 text-emerald-800 border-emerald-300'];
        }

        if ($this->is_overdue) {
            return ['Overdue', 'bg-rose-100 text-rose-800 border-rose-300'];
        }

        if ($this->status === 'partial') {
            return ['Partial', 'bg-amber-100 text-amber-800 border-amber-300'];
        }

        return ['Pending / Due', 'bg-blue-50 text-blue-700 border-blue-200'];
    }
}
