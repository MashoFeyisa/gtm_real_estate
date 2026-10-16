<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A remittance of collected VAT to the tax authority (SRS §170).
 *
 * The `vat_payable` account grows as VAT is charged on platform fees and shrinks
 * when a refund reverses it or when the platform pays the authority. This row is
 * the record of that payment, and the ledger journal "debit vat_payable, credit
 * cash" is what actually moves the balance.
 */
class VatRemittance extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'period_start',
        'period_end',
        'reference',
        'ledger_group',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
