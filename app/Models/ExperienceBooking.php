<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ExperienceBooking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'experience_id',
        'user_id',
        'booking_reference',
        'scheduled_date',
        'scheduled_time',
        'participants_count',
        'total_price',
        'currency',
        'contact_name',
        'contact_email',
        'contact_phone',
        'payment_status',
        'payment_method',
        'status',
        'special_requests',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'participants_count' => 'integer',
            'total_price' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ExperienceBooking $booking) {
            if (empty($booking->booking_reference)) {
                $dateCode = Carbon::now()->format('Ym');
                $randomSuffix = strtoupper(Str::random(5));
                $booking->booking_reference = "EXP-{$dateCode}-{$randomSuffix}";
            }
        });
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
