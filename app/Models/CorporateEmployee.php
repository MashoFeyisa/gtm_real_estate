<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'corporate_tenant_id',
        'user_id',
        'full_name',
        'employee_id_number',
        'email',
        'phone',
        'department',
        'designation',
        'status',
    ];

    public function corporateTenant(): BelongsTo
    {
        return $this->belongsTo(CorporateTenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaseAssignments(): HasMany
    {
        return $this->hasMany(CorporateLeaseAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(CorporateLeaseAssignment::class)
            ->where('status', 'active')
            ->latestOfMany();
    }
}
