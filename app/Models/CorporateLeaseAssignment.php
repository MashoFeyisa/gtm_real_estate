<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateLeaseAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'corporate_tenant_id',
        'lease_id',
        'corporate_employee_id',
        'unit_identifier',
        'assigned_from',
        'assigned_until',
        'monthly_rent_etb',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'assigned_from' => 'date',
            'assigned_until' => 'date',
            'monthly_rent_etb' => 'decimal:2',
        ];
    }

    public function corporateTenant(): BelongsTo
    {
        return $this->belongsTo(CorporateTenant::class);
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(CorporateEmployee::class, 'corporate_employee_id');
    }
}
