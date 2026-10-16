<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A recorded refund of a payment (SRS §132, §170).
 *
 * The row is the human-readable fact behind the refund's ledger journal: how
 * much went back, which slice of the platform fee and VAT was reversed, and the
 * credit note number that documents a VAT reversal.
 */
class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'amount',
        'fee_reversed',
        'vat_reversed',
        'reason',
        'ledger_group',
        'credit_note_number',
        'idempotency_key',
        'idempotency_hash',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_reversed' => 'decimal:2',
        'vat_reversed' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Whether this refund reversed any VAT and therefore carries a credit note.
     */
    public function hasCreditNote(): bool
    {
        return $this->credit_note_number !== null;
    }
}
