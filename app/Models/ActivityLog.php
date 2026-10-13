<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'actor_name',
        'actor_role',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an audit activity across the 6 platform actors.
     */
    public static function record(string $action, string $description, ?Model $entity = null, array $metadata = []): self
    {
        $user = auth()->user();

        return static::create([
            'user_id' => $user?->id,
            'actor_name' => $user?->name ?? 'System',
            'actor_role' => $user?->role ?? 'system',
            'action' => $action,
            'entity_type' => $entity ? class_basename($entity) : null,
            'entity_id' => $entity?->getKey(),
            'description' => $description,
            'metadata' => empty($metadata) ? null : $metadata,
        ]);
    }
}
