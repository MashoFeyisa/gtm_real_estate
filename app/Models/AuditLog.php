<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to log an audit event. Supports either passing $entity directly or ($entityType, $entityId).
     */
    public static function log(
        string $action,
        mixed $entity = null,
        ?string $entityType = null,
        mixed $entityId = null,
        mixed $oldValue = null,
        mixed $newValue = null,
        ?string $reason = null,
        ?User $user = null,
        ?Request $request = null
    ): self {
        $currentUser = $user ?? auth()->user();
        $currentRequest = $request ?? request();

        if ($entity instanceof Model) {
            $entityType = get_class($entity);
            $entityId = $entity->getKey();
        }

        return self::create([
            'user_id' => $currentUser?->id,
            'actor_name' => $currentUser?->name ?? 'System',
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId !== null ? (string) $entityId : null,
            'old_value' => is_array($oldValue) ? $oldValue : ($oldValue !== null ? ['value' => $oldValue] : null),
            'new_value' => is_array($newValue) ? $newValue : ($newValue !== null ? ['value' => $newValue] : null),
            'ip_address' => $currentRequest ? $currentRequest->ip() : null,
            'device' => $currentRequest ? substr((string) $currentRequest->userAgent(), 0, 255) : null,
            'reason' => $reason,
        ]);
    }
}
