<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'ticket_number',
        'title',
        'category',
        'priority',
        'status',
        'description',
        'photo_path',
        'location_notes',
        'scheduled_at',
        'resolved_at',
        'assigned_to',
        'resolution_notes',
        'estimated_cost',
        'actual_cost',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'resolved_at' => 'datetime',
        'estimated_cost' => 'float',
        'actual_cost' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (MaintenanceTicket $ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'MNT-'.strtoupper(uniqid());
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'plumbing' => 'Plumbing & Water',
            'electrical' => 'Electrical & Power',
            'internet' => 'Internet & Telecom',
            'appliance' => 'Kitchen & Appliances',
            'structural' => 'Structural & Roofing',
            'cleaning' => 'Housekeeping & Cleaning',
            default => 'General Maintenance',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'emergency' => 'bg-red-100 text-red-800 border-red-300',
            'high' => 'bg-amber-100 text-amber-800 border-amber-300',
            'medium' => 'bg-blue-100 text-blue-800 border-blue-300',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'completed', 'closed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'in_progress' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'assigned' => 'bg-teal-100 text-teal-800 border-teal-300',
            default => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }
}
