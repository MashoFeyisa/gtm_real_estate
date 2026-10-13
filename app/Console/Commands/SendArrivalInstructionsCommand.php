<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Notifications\GuestBookingNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendArrivalInstructionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stays:send-arrival-instructions {--date= : Custom target check-in date (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch arrival-day check-in instructions and house rules to guests for confirmed stay bookings (SRS §61)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDate = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : Carbon::today();

        $this->info("Scanning stay bookings for arrival date: {$targetDate->toDateString()}...");

        $bookings = Booking::with(['property.host', 'user'])
            ->whereDate('check_in_date', $targetDate)
            ->whereIn('status', ['confirmed', 'paid'])
            ->get();

        $dispatched = 0;

        foreach ($bookings as $booking) {
            $guestUser = $booking->user;
            if ($guestUser) {
                $guestUser->notify(new GuestBookingNotification($booking, 'arrival_instructions'));
            }

            AuditLog::log(
                action: 'stays.arrival_instructions_sent',
                entityType: 'Booking',
                entityId: $booking->id,
                newValue: [
                    'check_in_date' => $booking->check_in_date?->toDateString(),
                    'property' => $booking->property?->title,
                    'guest' => $booking->guest_name,
                ]
            );

            $dispatched++;
        }

        $this->info("Dispatched {$dispatched} arrival instruction notifications.");

        return Command::SUCCESS;
    }
}
