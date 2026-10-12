<?php

use App\Models\Agent;
use App\Models\Booking;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use App\Models\ExternalCalendarFeed;
use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use App\Services\CalendarSyncService;
use Carbon\Carbon;
use Database\Seeders\ExperienceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->hostUser = User::factory()->create([
        'name' => 'Sara Host',
        'email' => 'sara.host@realestate.com',
        'role' => 'host',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->hostUser->id,
        'name' => 'Addis Stays Hospitality',
        'email' => 'sara.host@realestate.com',
        'is_active' => true,
    ]);

    $this->property = Property::factory()->create([
        'title' => 'Bole Medhanialem Luxury Stay Penthouse',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'agent_id' => $this->agent->id,
    ]);

    $this->stayListing = Listing::create([
        'property_id' => $this->property->id,
        'listing_type' => 'stay',
        'title' => 'Bole Medhanialem Luxury Penthouse',
        'price' => 5000.00,
        'currency' => 'ETB',
        'pricing_period' => 'nightly',
        'status' => 'published',
    ]);
});

test('outbound icalendar export feed returns valid rfc 5545 calendar with reservation events', function () {
    // Create an active reservation
    Booking::create([
        'property_id' => $this->property->id,
        'listing_id' => $this->stayListing->id,
        'booking_reference' => 'STAY-AA-TEST-001',
        'guest_name' => 'Abebe Guest',
        'guest_email' => 'abebe@example.com',
        'guest_phone' => '+251 91 122 3344',
        'check_in_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(8)->format('Y-m-d'),
        'total_nights' => 3,
        'nightly_price' => 5000,
        'accommodation_total' => 15000,
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'total_amount' => 15000,
    ]);

    $response = $this->get(route('stays.calendar.ics', $this->property));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

    $content = $response->getContent();
    expect($content)->toContain('BEGIN:VCALENDAR');
    expect($content)->toContain('VERSION:2.0');
    expect($content)->toContain('BEGIN:VEVENT');
    expect($content)->toContain('DTSTART;VALUE=DATE:'.Carbon::now()->addDays(5)->format('Ymd'));
    expect($content)->toContain('DTEND;VALUE=DATE:'.Carbon::now()->addDays(8)->format('Ymd'));
    expect($content)->toContain('END:VCALENDAR');
});

test('calendar sync service accurately parses incoming rfc 5545 vevent records', function () {
    $service = app(CalendarSyncService::class);

    $sampleIcal = <<<'ICAL'
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Airbnb Inc//Hosting Calendar 0.8.8//EN
BEGIN:VEVENT
DTEND;VALUE=DATE:20261120
DTSTART;VALUE=DATE:20261115
UID:airbnb-reservation-987654@airbnb.com
SUMMARY:Reserved (Airbnb)
END:VEVENT
BEGIN:VEVENT
DTEND;VALUE=DATE:20261128
DTSTART;VALUE=DATE:20261125
UID:airbnb-reservation-112233@airbnb.com
SUMMARY:Reserved (Airbnb)
END:VEVENT
END:VCALENDAR
ICAL;

    $events = $service->parseIcalEvents($sampleIcal);

    expect($events)->toHaveCount(2);
    expect($events[0]['uid'])->toBe('airbnb-reservation-987654@airbnb.com');
    expect($events[0]['start_date'])->toBe('2026-11-15');
    expect($events[0]['end_date'])->toBe('2026-11-20');
    expect($events[1]['start_date'])->toBe('2026-11-25');
});

test('host can connect an external calendar feed and command syncs reservations', function () {
    $sampleIcal = <<<'ICAL'
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Booking.com//EN
BEGIN:VEVENT
DTEND;VALUE=DATE:20261210
DTSTART;VALUE=DATE:20261205
UID:bcom-reservation-555@booking.com
SUMMARY:Reserved (Booking.com)
END:VEVENT
END:VCALENDAR
ICAL;

    Http::fake([
        'https://ical.airbnb.com/calendar.ics' => Http::response($sampleIcal, 200),
    ]);

    $response = $this->actingAs($this->hostUser)->post(
        route('host.calendar-feeds.store', $this->property),
        [
            'name' => 'Airbnb Sync',
            'feed_url' => 'https://ical.airbnb.com/calendar.ics',
            'sync_interval_minutes' => 60,
        ]
    );

    $response->assertRedirect();
    $this->assertDatabaseHas('external_calendar_feeds', [
        'property_id' => $this->property->id,
        'name' => 'Airbnb Sync',
    ]);

    $feed = ExternalCalendarFeed::query()->first();
    expect($feed)->not->toBeNull();
    expect($feed->events_imported)->toBe(1);

    // Artisan command execution
    $this->artisan('stays:sync-calendars')
        ->assertSuccessful();
});

test('experiences marketplace index and show pages load with verified ethiopian tours', function () {
    $this->seed(ExperienceSeeder::class);

    $response = $this->get(route('experiences.index'));
    $response->assertOk()
        ->assertSee('Traditional Ethiopian Coffee Ceremony')
        ->assertSee('Harar Jugol');

    $experience = Experience::query()->where('slug', 'traditional-ethiopian-coffee-ceremony-roasting')->first();
    expect($experience)->not->toBeNull();

    $showResponse = $this->get(route('experiences.show', $experience));
    $showResponse->assertOk()
        ->assertSee('About this Experience')
        ->assertSee('ETB 1,500');
});

test('traveler can book an experience with telebirr payment method', function () {
    $this->seed(ExperienceSeeder::class);

    $traveler = User::factory()->create([
        'name' => 'John Traveler',
        'email' => 'john.traveler@example.com',
        'role' => 'traveler',
    ]);

    $experience = Experience::query()->first();

    $response = $this->actingAs($traveler)->post(route('experiences.book', $experience), [
        'scheduled_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
        'scheduled_time' => '09:00',
        'participants_count' => 2,
        'contact_name' => 'John Traveler',
        'contact_email' => 'john.traveler@example.com',
        'contact_phone' => '+251 91 100 2233',
        'payment_method' => 'telebirr',
        'special_requests' => 'Looking forward to learning about coffee roasting.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $booking = ExperienceBooking::query()->where('contact_email', 'john.traveler@example.com')->first();
    expect($booking)->not->toBeNull();
    expect($booking->participants_count)->toBe(2);
    expect($booking->total_price)->toBe($experience->price_etb * 2);
    expect($booking->status)->toBe('confirmed');
    expect($booking->payment_status)->toBe('paid');
});
