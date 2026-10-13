<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'tags',
        'author_name',
        'type',
        'status',
        'content',
        'excerpt',
        'seo_title',
        'seo_description',
        'user_id',
        'image_path',
        'social_image_path',
        'published_at',
        'scheduled_for',
        'related_post_ids',
        'job_location',
        'job_type',
        'salary_range',
        'requirements',
        'experience_level',
        'education_level',
        'apply_link',
        'application_deadline',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'application_deadline' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the absolute or public asset URL for the primary post/job image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        if (str_starts_with($this->image_path, 'images/')) {
            return asset($this->image_path);
        }

        return asset('storage/'.$this->image_path);
    }

    /**
     * Get the absolute or public asset URL for the social sharing image.
     */
    public function getSocialImageUrlAttribute(): ?string
    {
        if (! $this->social_image_path) {
            return null;
        }

        if (str_starts_with($this->social_image_path, 'http://') || str_starts_with($this->social_image_path, 'https://')) {
            return $this->social_image_path;
        }

        if (str_starts_with($this->social_image_path, 'images/')) {
            return asset($this->social_image_path);
        }

        return asset('storage/'.$this->social_image_path);
    }

    /**
     * Scope query to only include active/unexpired jobs, or regular posts.
     * When a post is of type 'job' and has an application_deadline, it must be today or in the future.
     */
    public function scopeActiveNotExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('type', '!=', 'job')
                ->orWhereNull('application_deadline')
                ->orWhereDate('application_deadline', '>=', now()->toDateString());
        });
    }
}
