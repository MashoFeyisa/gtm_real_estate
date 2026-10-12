<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperEscrowDisbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'milestone_id',
        'requested_by',
        'verified_by',
        'amount_etb',
        'status',
        'bank_reference',
        'inspection_notes',
        'disbursed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_etb' => 'decimal:2',
            'disbursed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ConstructionMilestone::class, 'milestone_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
