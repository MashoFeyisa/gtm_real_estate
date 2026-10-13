<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Campaign extends Model
{
    protected $fillable = [
        'agent_id',
        'user_id',
        'property_id',
        'project_id',
        'title',
        'slug',
        'campaign_type',
        'target_audience',
        'headline',
        'description',
        'marketing_copy',
        'banner_image_path',
        'budget',
        'spent',
        'start_date',
        'end_date',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'views_count',
        'clicks_count',
        'inquiries_count',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'reviewed_at' => 'datetime',
        'views_count' => 'integer',
        'clicks_count' => 'integer',
        'inquiries_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Campaign $campaign) {
            if (empty($campaign->slug)) {
                $base = Str::slug($campaign->title ?: 'campaign');
                $slug = $base.'-'.Str::lower(Str::random(6));
                $campaign->slug = $slug;
            }
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(CampaignLead::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'under_review';
    }

    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'published']);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'published' => ['Published & Live', 'bg-emerald-100 text-emerald-800 border-emerald-300'],
            'approved' => ['Approved', 'bg-blue-100 text-blue-800 border-blue-300'],
            'under_review' => ['Pending Review', 'bg-amber-100 text-amber-800 border-amber-300'],
            'rejected' => ['Revision Requested', 'bg-rose-100 text-rose-800 border-rose-300'],
            'completed' => ['Completed', 'bg-slate-100 text-slate-700 border-slate-300'],
            'paused' => ['Paused', 'bg-yellow-100 text-yellow-800 border-yellow-300'],
            default => ['Draft', 'bg-slate-100 text-slate-600 border-slate-200'],
        };
    }
}
