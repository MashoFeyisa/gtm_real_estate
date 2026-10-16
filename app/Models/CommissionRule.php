<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A dated commission override (SRS §99).
 *
 * The platform default commission is narrowed by the most specific live rule:
 * agent beats property type, which beats company, which beats the default.
 * Like `FinanceRate`, a rule is a dated fact and is never edited in place.
 */
class CommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'property_type',
        'agent_id',
        'rate',
        'effective_from',
        'effective_to',
        'created_by',
        'reason',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The most specific override in force at the given moment, or null.
     *
     * @return float|null the overriding rate, or null to use the default
     */
    public static function overrideFor(
        ?int $agentId = null,
        ?string $propertyType = null,
        ?int $companyId = null,
        ?\DateTimeInterface $at = null
    ): ?float {
        $at = $at ?? now();

        $live = static::query()
            ->where('effective_from', '<=', $at)
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>=', $at))
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();

        $scopes = [
            fn (CommissionRule $rule): bool => $agentId !== null && (int) $rule->agent_id === $agentId,
            fn (CommissionRule $rule): bool => $propertyType !== null
                && $rule->agent_id === null
                && strcasecmp((string) $rule->property_type, (string) $propertyType) === 0,
            fn (CommissionRule $rule): bool => $companyId !== null
                && $rule->agent_id === null
                && $rule->property_type === null
                && (int) $rule->company_id === $companyId,
        ];

        foreach ($scopes as $matches) {
            $rule = $live->first($matches);

            if ($rule) {
                return (float) $rule->rate;
            }
        }

        return null;
    }
}
