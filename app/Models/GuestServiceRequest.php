<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestServiceRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getServiceTypeLabelAttribute(): string
    {
        return match ($this->service_type) {
            'room_cleaning' => '🧹 Room Cleaning / Housekeeping',
            'extra_towels' => '🧺 Extra Linens & Towels',
            'airport_transfer' => '🚕 Airport Shuttle Transfer',
            'wake_up_call' => '⏰ Wake-Up Call',
            'maintenance' => '🔧 In-Room Repair / Maintenance',
            'inquiry' => 'ℹ️ Guest Inquiry / Concierge',
            default => '🛎️ Guest Service Assistance',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'in_progress' => 'bg-blue-100 text-blue-800 border-blue-300',
            'cancelled' => 'bg-slate-100 text-slate-700 border-slate-300',
            default => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }
}
