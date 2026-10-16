<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A dated tax/commission rate (SRS §171).
 *
 * A row is a fact, not a setting: "from `effective_from` (until `effective_to`,
 * if ever), `key` is `rate` percent". The rate in force for a transaction is the
 * newest row whose window covers that moment, so a change is recorded by adding
 * a row rather than editing one.
 */
class FinanceRate extends Model
{
    use HasFactory;

    public const PLATFORM_FEE = 'platform_fee';

    public const VAT = 'vat';

    public const WITHHOLDING = 'withholding';

    public const COMMISSION = 'commission';

    public const EXPERIENCE_FEE = 'experience_fee';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PENDING = 'pending';

    protected $fillable = [
        'key',
        'rate',
        'effective_from',
        'effective_to',
        'location',
        'service',
        'created_by',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'approved_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * The rate for `key` in force at the given moment, or null when no dated
     * rule covers it (the caller then falls back to the shipped default).
     *
     * Only approved rows are live: a row awaiting second-person approval must
     * never move money.
     */
    public static function rateFor(string $key, ?\DateTimeInterface $at = null): ?float
    {
        $at = $at ?? now();

        $rule = static::query()
            ->where('key', $key)
            ->where('status', self::STATUS_APPROVED)
            ->where('effective_from', '<=', $at)
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>=', $at))
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        return $rule ? (float) $rule->rate : null;
    }

    /**
     * The rate currently in force for `key` across every status, used to log
     * the old value on a change (the caller compares it with the new one).
     */
    public static function currentRate(string $key, ?\DateTimeInterface $at = null): ?float
    {
        $at = $at ?? now();

        $rule = static::query()
            ->where('key', $key)
            ->where('effective_from', '<=', $at)
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>=', $at))
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        return $rule ? (float) $rule->rate : null;
    }
}
