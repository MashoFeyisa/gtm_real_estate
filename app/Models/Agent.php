<?php

namespace App\Models;

use App\Services\CommissionSplitService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class Agent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected $appends = ['photo_url', 'initials', 'average_rating', 'feedback_count'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'commission_rate' => 'float',
            'split_percentage' => 'float',
            'license_verified' => 'boolean',
            'service_locations' => 'array',
            'languages' => 'array',
        ];
    }

    public function getTierDetailsAttribute(): array
    {
        return app(CommissionSplitService::class)->getTierDetails($this->tier);
    }

    public function getEffectiveSplitPercentageAttribute(): float
    {
        return (float) ($this->split_percentage ?: $this->tier_details['default_split']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

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

    public function user(): BelongsTo
    {
        return $this->account();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function viewings(): HasMany
    {
        return $this->hasMany(PropertyViewing::class)->latest();
    }

    public function offers(): HasMany
    {
        return $this->hasMany(PropertyOffer::class)->latest();
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class)->latest();
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(AgentClient::class)->latest();
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(AgentFeedback::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function pendingCommissions(): HasMany
    {
        return $this->commissions()->where('status', 'pending');
    }

    public function getTotalCommissionOwedAttribute(): float
    {
        return (float) $this->pendingCommissions()->sum('commission_amount');
    }

    public function getTotalCommissionPaidAttribute(): float
    {
        return (float) $this->commissions()->where('status', 'paid')->sum('commission_amount');
    }

    public function approvedFeedbacks(): HasMany
    {
        return $this->feedbacks()->where('is_approved', true)->latest();
    }

    public function setPasswordAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['password'] = null;

            return;
        }

        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
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

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class)->latest();
    }
}
