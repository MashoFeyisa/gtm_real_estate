<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateTenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'tax_id',
        'contact_person',
        'contact_email',
        'contact_phone',
        'industry',
        'billing_address',
        'city',
        'credit_limit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(CorporateEmployee::class);
    }

    public function leaseAssignments(): HasMany
    {
        return $this->hasMany(CorporateLeaseAssignment::class);
    }

    public function getTotalMonthlyRentCommitmentAttribute(): float
    {
        return (float) $this->leaseAssignments()
            ->where('status', 'active')
            ->sum('monthly_rent_etb');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
