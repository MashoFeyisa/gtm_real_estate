<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'property_id',
        'agent_id',
        'type',
        'name',
        'email',
        'phone',
        'message',
        'offer_amount',
        'lease_start',
        'lease_months',
        'status',
        'agent_note',
        'agreement_path',
        'agreed_at',
        'submitted_to_admin_at',
        'admin_viewed_at',
        'admin_status',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'offer_amount' => 'float',
            'agreed_at' => 'datetime',
            'lease_start' => 'date',
            'submitted_to_admin_at' => 'datetime',
            'admin_viewed_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function isRental(): bool
    {
        return $this->type === 'rent';
    }

    public function hasAgreement(): bool
    {
        return $this->agreement_path !== null;
    }

    public function isSubmittedToAdmin(): bool
    {
        return $this->submitted_to_admin_at !== null;
    }
}
