<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An inbound payment-gateway callback record (SRS §83).
 *
 * Every call is logged - including ones rejected for a missing or invalid
 * signature - so finance can audit gateway traffic and webhook probing.
 */
class PaymentGatewayLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'event',
        'outcome',
        'gateway_reference',
        'transaction_number',
        'payment_id',
        'signature',
        'provider_timestamp',
        'signature_valid',
        'response_code',
        'payload',
        'ip_address',
        'user_agent',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'signature_valid' => 'boolean',
            'response_code' => 'integer',
        ];
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('outcome', 'accepted');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('outcome', 'rejected');
    }
}
