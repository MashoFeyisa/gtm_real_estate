<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorPortfolio extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_valuation' => 'decimal:2',
        'monthly_rental_income' => 'decimal:2',
        'monthly_expenses' => 'decimal:2',
        'occupancy_rate' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Estimated annual gross yield (percentage).
     */
    public function getGrossYieldAttribute(): float
    {
        if ($this->current_valuation > 0) {
            return round((($this->monthly_rental_income * 12) / $this->current_valuation) * 100, 2);
        }

        return 0.0;
    }

    /**
     * Estimated annual net yield (percentage).
     */
    public function getNetYieldAttribute(): float
    {
        if ($this->current_valuation > 0) {
            $annualNet = ($this->monthly_rental_income - $this->monthly_expenses) * 12;

            return round(($annualNet / $this->current_valuation) * 100, 2);
        }

        return 0.0;
    }
}
