<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentFeedback extends Model
{
    use SoftDeletes;

    protected $table = 'agent_feedbacks';

    protected $fillable = [
        'agent_id',
        'name',
        'email',
        'rating',
        'message',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
