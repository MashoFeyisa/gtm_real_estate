<?php

namespace App\Console\Commands;

use App\Models\ExternalCalendarFeed;
use App\Services\CalendarSyncService;
use Illuminate\Console\Command;

class SyncExternalCalendarsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stays:sync-calendars {--feed= : Specific feed ID to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all external short-term stay iCal calendar feeds (Airbnb, Booking.com, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(CalendarSyncService $calendarService): int
    {
        $feedId = $this->option('feed');

        $query = ExternalCalendarFeed::query();
        if ($feedId) {
            $query->where('id', $feedId);
        }

        $feeds = $query->get();

        if ($feeds->isEmpty()) {
            $this->info('No external calendar feeds found.');

            return self::SUCCESS;
        }

        $this->info("Starting sync for {$feeds->count()} calendar feeds...");

        $totalImported = 0;
        foreach ($feeds as $feed) {
            $this->line("Syncing feed #{$feed->id}: {$feed->name} ({$feed->feed_url})...");
            $imported = $calendarService->syncFeed($feed);
            $totalImported += $imported;
            $this->info("Imported {$imported} reservations from {$feed->name}. Status: {$feed->fresh()->sync_status}");
        }

        $this->info("Completed calendar synchronization. Total reservations synced: {$totalImported}");

        return self::SUCCESS;
    }
}
