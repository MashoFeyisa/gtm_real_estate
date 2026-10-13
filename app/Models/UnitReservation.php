<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UnitReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_reference',
        'project_id',
        'unit_id',
        'user_id',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'status',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (UnitReservation $reservation) {
            if (empty($reservation->reservation_reference)) {
                $dateCode = Carbon::now()->format('Ym');
                $randomSuffix = strtoupper(Str::random(4));
                $reservation->reservation_reference = "RES-DEV-AA-{$dateCode}-{$randomSuffix}";
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProjectUnit::class, 'unit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
