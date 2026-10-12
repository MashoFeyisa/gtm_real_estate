<?php

namespace App\Http\Controllers;

use App\Models\ExternalCalendarFeed;
use App\Models\Property;
use App\Services\CalendarSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StayCalendarSyncController extends Controller
{
    /**
     * Export an RFC 5545 iCalendar feed for external platforms (Airbnb, Booking.com, etc.).
     */
    public function export(Property $property, CalendarSyncService $calendarService): Response
    {
        $icalContent = $calendarService->generateIcal($property);

        return response($icalContent, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="stay-calendar-'.$property->id.'.ics"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Save a new external iCal calendar feed URL to synchronize.
     */
    public function storeFeed(Request $request, Property $property): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'feed_url' => ['required', 'url', 'max:1000'],
            'sync_interval_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
        ]);

        $feed = ExternalCalendarFeed::create([
            'property_id' => $property->id,
            'listing_id' => $property->listings()->where('listing_type', 'stay')->first()?->id,
            'name' => $validated['name'],
            'feed_url' => $validated['feed_url'],
            'sync_interval_minutes' => $validated['sync_interval_minutes'] ?? 60,
        ]);

        // Attempt initial sync
        app(CalendarSyncService::class)->syncFeed($feed);

        return back()->with('success', "External calendar \"{$feed->name}\" connected and synchronized.");
    }

    /**
     * Trigger manual synchronization on demand.
     */
    public function syncFeed(ExternalCalendarFeed $feed, CalendarSyncService $calendarService): RedirectResponse|JsonResponse
    {
        $imported = $calendarService->syncFeed($feed);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'imported' => $imported,
                'status' => $feed->fresh()->sync_status,
            ]);
        }

        return back()->with('success', "Synchronized {$imported} events from {$feed->name}.");
    }

    /**
     * Remove an external calendar feed.
     */
    public function destroyFeed(ExternalCalendarFeed $feed): RedirectResponse
    {
        $name = $feed->name;
        $feed->delete();

        return back()->with('success', "Calendar feed \"{$name}\" removed.");
    }
}
