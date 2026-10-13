<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperPaymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'installment_name',
        'percentage',
        'milestone_trigger',
        'order_index',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'order_index' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
