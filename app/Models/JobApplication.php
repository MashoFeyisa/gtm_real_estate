<?php

namespace App\Models;

use Database\Factories\JobApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    /** @use HasFactory<JobApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'blog_post_id',
        'job_title',
        'full_name',
        'email',
        'phone',
        'current_location',
        'experience_years',
        'education_level',
        'portfolio_url',
        'expected_salary',
        'available_start_date',
        'cover_letter',
        'resume_path',
        'resume_original_name',
        'resume_mime_type',
        'resume_size',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'resume_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function blogPost()
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function getResumeUrlAttribute(): ?string
    {
        if (! $this->resume_path) {
            return null;
        }

        return asset('storage/'.$this->resume_path);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (! $this->resume_size) {
            return 'Unknown size';
        }

        if ($this->resume_size >= 1048576) {
            return number_format($this->resume_size / 1048576, 2).' MB';
        }

        return number_format($this->resume_size / 1024, 0).' KB';
    }

    public function getFileExtensionAttribute(): string
    {
        return strtolower(pathinfo((string) $this->resume_original_name, PATHINFO_EXTENSION) ?: 'pdf');
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->file_extension === 'pdf';
    }

    public function getIsDocxAttribute(): bool
    {
        return in_array($this->file_extension, ['docx', 'doc'], true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
