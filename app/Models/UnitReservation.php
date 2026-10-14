<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
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
        'payment_plan_id',
        'deposit_amount',
        'contract_date',
        'notes',
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'contract_date' => 'date',
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

    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(DeveloperPaymentPlan::class, 'payment_plan_id');
    }

    /**
     * Expire pending off-plan unit reservations and release held units back to available.
     */
    public static function expireUnpaidHolds(int $holdHours = 72): int
    {
        $cutoff = Carbon::now()->subHours($holdHours);

        return DB::transaction(function () use ($cutoff) {
            $expiredReservations = static::query()
                ->where('status', 'pending')
                ->where('created_at', '<=', $cutoff)
                ->lockForUpdate()
                ->get();

            $count = 0;
            foreach ($expiredReservations as $reservation) {
                $reservation->update(['status' => 'cancelled']);
                if ($reservation->unit_id) {
                    ProjectUnit::where('id', $reservation->unit_id)
                        ->where('status', 'reserved')
                        ->update(['status' => 'available']);
                }
                $count++;
            }

            return $count;
        });
    }
}
