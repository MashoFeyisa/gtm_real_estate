<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'ledger_code',
        'payment_id',
        'transaction_group',
        'entry_type',
        'category',
        'amount',
        'rate_snapshot',
        'is_legacy',
        'currency',
        'description',
        'reference',
        'user_id',
        'party_user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'rate_snapshot' => 'array',
        'is_legacy' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (FinancialLedger $ledger) {
            if (empty($ledger->ledger_code)) {
                $year = date('Y');
                $random = strtoupper(substr(uniqid(), -6));
                $ledger->ledger_code = "LED-{$year}-{$random}";
            }
        });

        static::updating(function () {
            throw new \RuntimeException('Financial records are immutable and cannot be overwritten. Record a financial adjustment entry instead.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Financial records are immutable and cannot be deleted.');
        });
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(User::class, 'party_user_id');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'booking_revenue' => 'Booking Revenue',
            'platform_fee' => 'Platform Service Fee',
            'vat_payable' => 'VAT Payable',
            'refund_adjustment' => 'Refund Adjustment',
            'agent_commission' => 'Agent Commission',
            'agent_payable' => 'Agent Payable',
            'agent_receivable' => 'Agent Recovery Receivable',
            'website_commission' => 'Website Commission',
            'developer_deposit' => 'Developer Project Deposit',
            'tax_withholding' => 'Tax / VAT Withholding',
            'payout' => 'Host / Owner Payout',
            'earnest_deposit' => 'Earnest Deposit Held',
            'cash' => 'Cash / Bank Received',
            default => ucfirst(str_replace('_', ' ', (string) $this->category)),
        };
    }
}
