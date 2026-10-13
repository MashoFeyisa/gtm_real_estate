<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConstructionMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'milestone_name',
        'percentage',
        'target_date',
        'status',
        'order_index',
    ];

    protected $casts = [
        'percentage' => 'integer',
        'order_index' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
