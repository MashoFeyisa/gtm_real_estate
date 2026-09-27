<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class Agent extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    protected $appends = ['photo_url', 'initials', 'average_rating', 'feedback_count'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function pendingOrders(): HasMany
    {
        return $this->orders()->where('status', 'pending');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(AgentFeedback::class);
    }

    public function approvedFeedbacks(): HasMany
    {
        return $this->feedbacks()->where('is_approved', true)->latest();
    }

    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = $value !== null ? Hash::make($value) : null;
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return asset('storage/'.$this->photo_path);
        }

        return asset('images/agent-placeholder.svg');
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->take(2)
            ->implode('');
    }

    public function getAverageRatingAttribute(): float
    {
        return (float) $this->approvedFeedbacks()->avg('rating');
    }

    public function getFeedbackCountAttribute(): int
    {
        return $this->approvedFeedbacks()->count();
    }
}
