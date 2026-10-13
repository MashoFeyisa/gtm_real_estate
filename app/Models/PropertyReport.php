<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'report_number',
        'subject',
        'reason',
        'description',
        'contact_email',
        'status',
        'resolved_by',
        'resolution_notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (PropertyReport $report) {
            if (empty($report->report_number)) {
                $report->report_number = 'REP-'.strtoupper(uniqid());
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

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'scam' => 'Potential Scam / Fraud',
            'fake_property' => 'Fictitious / Non-Existent Property',
            'duplicate' => 'Duplicate / Stolen Listing',
            'wrong_price' => 'Inaccurate / Misleading Price',
            'unavailable' => 'Property Already Sold / Unavailable',
            'misleading' => 'Misleading Description / Photos',
            'suspicious_documents' => 'Forged / Inauthentic Documents',
            'inappropriate_content' => 'Inappropriate Photos or Content',
            'service_complaint' => 'Service / Front Desk Complaint',
            'agent_misconduct' => 'Broker / Agent Misconduct',
            default => 'General Violation / Report',
        };
    }
}
