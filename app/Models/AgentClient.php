<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'name',
        'email',
        'phone',
        'lead_source',
        'stage',
        'budget',
        'preferred_type',
        'preferred_location',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
