<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ExternalCalendarFeed;
use App\Models\Listing;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalendarSyncService
{
    /**
     * Generate an RFC 5545 compliant iCalendar (.ics) string for a property or stay listing.
     */
    public function generateIcal(Property|Listing $item): string
    {
        $propertyId = $item instanceof Property ? $item->id : $item->property_id;
        $title = $item->title ?? 'GTM Real Estate Stay';

        $bookings = Booking::query()
            ->where('property_id', $propertyId)
            ->whereIn('status', ['confirmed', 'hold', 'checked_in'])
            ->where('check_out_date', '>=', now()->subDays(30))
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//GTM Real Estate//Stay Calendar 2.0//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.addcslashes($title, ',;\\'),
            'X-WR-TIMEZONE:Africa/Addis_Ababa',
        ];

        $nowTimestamp = gmdate('Ymd\THis\Z');

        foreach ($bookings as $booking) {
            $startDate = Carbon::parse($booking->check_in_date)->format('Ymd');
            $endDate = Carbon::parse($booking->check_out_date)->format('Ymd');
            $uid = sprintf('booking-%s-%d@gtmrealestate.com', $booking->booking_reference ?: 'REF', $booking->id);

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$uid;
            $lines[] = 'DTSTAMP:'.$nowTimestamp;
            $lines[] = 'DTSTART;VALUE=DATE:'.$startDate;
            $lines[] = 'DTEND;VALUE=DATE:'.$endDate;
            $lines[] = 'SUMMARY:Reserved ('.($booking->booking_reference ?: 'GTM Stay').')';
            $lines[] = 'STATUS:CONFIRMED';
            $lines[] = 'TRANSP:OPAQUE';
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    /**
     * Parse raw iCalendar (.ics) text into structured event records.
     *
     * @return list<array{uid: string, start_date: string, end_date: string, summary: string}>
     */
    public function parseIcalEvents(string $icalContent): array
    {
        $events = [];
        $lines = preg_split("/\r\n|\n|\r/", $icalContent);

        $currentEvent = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === 'BEGIN:VEVENT') {
                $currentEvent = [
                    'uid' => '',
                    'start_date' => '',
                    'end_date' => '',
                    'summary' => 'External Reservation',
                ];

                continue;
            }

            if ($line === 'END:VEVENT' && $currentEvent) {
                if (! empty($currentEvent['start_date']) && ! empty($currentEvent['end_date'])) {
                    $events[] = $currentEvent;
                }
                $currentEvent = null;

                continue;
            }

            if (! $currentEvent) {
                continue;
            }

            if (str_starts_with($line, 'UID:')) {
                $currentEvent['uid'] = substr($line, 4);
            } elseif (preg_match('/^DTSTART.*:([0-9]{8})/i', $line, $m)) {
                $currentEvent['start_date'] = substr($m[1], 0, 4).'-'.substr($m[1], 4, 2).'-'.substr($m[1], 6, 2);
            } elseif (preg_match('/^DTEND.*:([0-9]{8})/i', $line, $m)) {
                $currentEvent['end_date'] = substr($m[1], 0, 4).'-'.substr($m[1], 4, 2).'-'.substr($m[1], 6, 2);
            } elseif (str_starts_with($line, 'SUMMARY:')) {
                $currentEvent['summary'] = substr($line, 8);
            }
        }

        return $events;
    }

    /**
     * Pull remote iCal feed and synchronize blocked dates into the booking table.
     */
    public function syncFeed(ExternalCalendarFeed $feed): int
    {
        $feed->update(['sync_status' => 'syncing']);

        try {
            $response = Http::timeout(10)->get($feed->feed_url);

            if (! $response->successful()) {
                throw new \RuntimeException('Failed to fetch calendar feed: HTTP '.$response->status());
            }

            $events = $this->parseIcalEvents($response->body());
            $importedCount = 0;

            foreach ($events as $event) {
                $checkIn = Carbon::parse($event['start_date']);
                $checkOut = Carbon::parse($event['end_date']);

                if ($checkOut->isBefore(now())) {
                    continue;
                }

                $nights = max(1, $checkIn->diffInDays($checkOut));

                Booking::updateOrCreate(
                    [
                        'property_id' => $feed->property_id,
                        'booking_reference' => 'EXT-'.substr(md5($event['uid'] ?: $event['start_date']), 0, 10),
                    ],
                    [
                        'listing_id' => $feed->listing_id,
                        'guest_name' => 'External Calendar Guest',
                        'guest_email' => 'external.sync@gtmrealestate.com',
                        'guest_phone' => '+251 90 000 0000',
                        'check_in_date' => $checkIn->toDateString(),
                        'check_out_date' => $checkOut->toDateString(),
                        'total_nights' => $nights,
                        'guests_count' => 1,
                        'nightly_price' => 0,
                        'accommodation_total' => 0,
                        'total_amount' => 0,
                        'status' => 'confirmed',
                        'payment_status' => 'paid',
                    ]
                );

                $importedCount++;
            }

            $feed->update([
                'last_synced_at' => now(),
                'sync_status' => 'success',
                'last_error' => null,
                'events_imported' => $importedCount,
            ]);

            return $importedCount;
        } catch (\Throwable $e) {
            Log::warning('External calendar feed sync failed: '.$e->getMessage(), ['feed_id' => $feed->id]);

            $feed->update([
                'sync_status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);

            return 0;
        }
    }
}
