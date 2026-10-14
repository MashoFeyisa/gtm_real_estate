<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\UnitReservation;
use Illuminate\Console\Command;

class ExpireReservationHoldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:expire-holds 
                            {--stay-hours=24 : Hold duration in hours for short stay bookings}
                            {--unit-hours=72 : Hold duration in hours for off-plan unit reservations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire pending stay bookings and off-plan unit reservations whose payment hold window has passed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $stayHours = (int) $this->option('stay-hours');
        $unitHours = (int) $this->option('unit-hours');

        $this->info("Expiring pending stay bookings older than {$stayHours} hours...");
        $expiredBookings = Booking::expireUnpaidHolds($stayHours);
        $this->line(" - Released {$expiredBookings} expired stay booking(s).");

        $this->info("Expiring pending off-plan unit reservations older than {$unitHours} hours...");
        $expiredUnits = UnitReservation::expireUnpaidHolds($unitHours);
        $this->line(" - Released {$expiredUnits} expired unit reservation(s).");

        $this->info('Hold expiration process completed successfully.');

        return Command::SUCCESS;
    }
}
