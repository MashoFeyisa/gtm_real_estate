<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'property_price' => 'float',
            'commission_rate' => 'float',
            'commission_amount' => 'float',
            'withholding_tax_rate' => 'float',
            'withholding_tax_amount' => 'float',
            'net_payout_amount' => 'float',
            'total_deal_commission' => 'float',
            'listing_agent_amount' => 'float',
            'selling_agent_amount' => 'float',
            'referral_amount' => 'float',
            'company_retained_amount' => 'float',
            'split_breakdown' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function listingAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'listing_agent_id');
    }

    public function sellingAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'selling_agent_id');
    }

    public function referralAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'referral_agent_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function disbursedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function isCoBrokered(): bool
    {
        return $this->split_type === 'co_brokered';
    }

    public function hasReferral(): bool
    {
        return $this->referral_agent_id !== null && (float) $this->referral_amount > 0;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isWaived(): bool
    {
        return $this->status === 'waived';
    }

    /**
     * Settle commission payout deducting Ethiopian withholding tax (SRS §126–§135).
     */
    public function settlePayout(User $disbursedBy, string $method, ?string $reference = null, ?string $note = null, float $taxRate = 2.0): self
    {
        $gross = (float) $this->commission_amount;
        $whtAmount = round($gross * ($taxRate / 100), 2);
        $netPayout = round($gross - $whtAmount, 2);

        $year = date('Y');
        $random = strtoupper(substr(uniqid(), -6));
        $voucherNumber = $this->voucher_number ?: "VCH-COMM-{$year}-{$random}";

        $this->update([
            'status' => 'paid',
            'withholding_tax_rate' => $taxRate,
            'withholding_tax_amount' => $whtAmount,
            'net_payout_amount' => $netPayout,
            'payout_method' => $method,
            'payout_reference' => $reference,
            'admin_note' => $note ?: $this->admin_note,
            'disbursed_by' => $disbursedBy->id,
            'voucher_number' => $voucherNumber,
            'paid_at' => now(),
        ]);

        // Record auditable debit entry into Financial Ledger
        FinancialLedger::create([
            'entry_type' => 'debit',
            'category' => 'agent_commission',
            'amount' => $gross,
            'currency' => 'ETB',
            'description' => "Commission disbursed to {$this->agent?->name} ({$this->voucher_number}). Net: {$netPayout} ETB, WHT (2%): {$whtAmount} ETB",
            'reference' => $this->payout_reference ?: $this->voucher_number,
            'user_id' => $disbursedBy->id,
        ]);

        return $this;
    }
}
