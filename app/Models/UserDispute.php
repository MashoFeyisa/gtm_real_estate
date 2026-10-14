<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispute_code',
        'user_id',
        'respondent_id',
        'property_id',
        'booking_id',
        'title',
        'description',
        'stage',
        'evidence_notes',
        'evidence_files',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'evidence_files' => 'array',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (UserDispute $dispute) {
            if (empty($dispute->dispute_code)) {
                $dispute->dispute_code = 'DSP-'.strtoupper(uniqid());
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondent_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
