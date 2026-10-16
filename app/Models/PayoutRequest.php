<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'payout_code',
        'user_id',
        'role',
        'gross_amount',
        'withholding_tax',
        'net_amount',
        'payment_method',
        'bank_name',
        'account_number',
        'status',
        'voucher_number',
        'disbursement_mode',
        'approved_by',
        'approved_at',
        'idempotency_key',
        'idempotency_hash',
        'rejection_reason',
        'processed_by',
        'processed_at',
        'transaction_reference',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isManualDisbursement(): bool
    {
        return $this->disbursement_mode === 'manual';
    }
}
