<?php

use App\Models\Booking;
use App\Models\FrontDeskShift;
use App\Models\GuestServiceRequest;
use App\Models\Inquiry;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\PropertyReview;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->property = Property::create([
        'title' => 'Bole Grand Luxury Guest House',
        'slug' => 'bole-grand-luxury-guest-house',
        'type' => 'stay',
        'property_category' => 'guest_house',
        'price' => 3500,
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanealem, Atlas',
        'is_active' => true,
    ]);

    $this->receptionistUser = User::create([
        'name' => 'Sara Front Desk',
        'email' => 'sara.reception@example.com',
        'password' => Hash::make('password123'),
        'role' => 'receptionist',
        'assigned_property_id' => $this->property->id,
    ]);

    $this->adminUser = User::create([
        'name' => 'System Admin',
        'email' => 'admin.reception@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->financeUser = User::create([
        'name' => 'Dawit Finance Officer',
        'email' => 'dawit.finance@example.com',
        'password' => Hash::make('password123'),
        'role' => 'finance_officer',
    ]);

    $this->clientUser = User::create([
        'name' => 'Abebe Guest',
        'email' => 'abebe.guest@example.com',
        'password' => Hash::make('password123'),
        'role' => 'user',
    ]);
});

test('unauthenticated users are redirected to login when accessing receptionist desk', function () {
    $response = $this->get(route('receptionist.dashboard'));
    $response->assertRedirect(route('login'));
});

test('regular clients are forbidden from accessing receptionist desk', function () {
    $response = $this->actingAs($this->clientUser)->get(route('receptionist.dashboard'));
    $response->assertStatus(403);
});

test('receptionists and admins can access front desk dashboard', function () {
    $response = $this->actingAs($this->receptionistUser)->get(route('receptionist.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Receptionist Desk &amp; Guest Management', false);

    $adminResponse = $this->actingAs($this->adminUser)->get(route('receptionist.dashboard'));
    $adminResponse->assertStatus(200);
});

test('logging in as receptionist redirects to receptionist dashboard', function () {
    $response = $this->post('/login', [
        'email' => 'sara.reception@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/receptionist');
});

test('receptionist can check in an arriving guest with room number and id verification', function () {
    $booking = Booking::create([
        'user_id' => $this->clientUser->id,
        'property_id' => $this->property->id,
        'guest_name' => 'Abebe Guest',
        'guest_email' => 'abebe.guest@example.com',
        'guest_phone' => '+251911223344',
        'check_in_date' => Carbon::today()->toDateString(),
        'check_out_date' => Carbon::tomorrow()->toDateString(),
        'total_nights' => 1,
        'guests_count' => 2,
        'nightly_price' => 3500,
        'accommodation_total' => 3500,
        'total_amount' => 3500,
        'currency' => 'ETB',
        'status' => 'confirmed',
        'payment_status' => 'pending',
    ]);

    $response = $this->actingAs($this->receptionistUser)
        ->post(route('receptionist.check-in', $booking), [
            'room_number' => 'Suite 204',
            'guest_id_number' => 'ET-KEBELE-998877',
            'payment_method' => 'telebirr',
            'amount_collected' => 3500,
            'payment_reference' => 'TB-20261006-991',
        ]);

    $response->assertSessionHas('success');

    $booking->refresh();
    expect($booking->status)->toBe('checked_in')
        ->and($booking->room_number)->toBe('Suite 204')
        ->and($booking->guest_id_number)->toBe('ET-KEBELE-998877')
        ->and($booking->checked_in_at)->not->toBeNull()
        ->and($booking->payment_status)->toBe('paid');

    $this->assertDatabaseHas('payments', [
        'payable_id' => $booking->id,
        'payable_type' => Booking::class,
        'payment_method' => 'telebirr',
        'amount' => 3500,
        'status' => 'confirmed',
    ]);

    $this->assertDatabaseHas('financial_ledgers', [
        'category' => 'booking_revenue',
        'entry_type' => 'credit',
        'amount' => 3500,
    ]);
});

test('receptionist can complete guest check-out', function () {
    $booking = Booking::create([
        'user_id' => $this->clientUser->id,
        'property_id' => $this->property->id,
        'room_number' => 'Room 102',
        'guest_id_number' => 'PASS-ET-12345',
        'guest_name' => 'Abebe Guest',
        'guest_email' => 'abebe.guest@example.com',
        'guest_phone' => '+251911223344',
        'check_in_date' => Carbon::yesterday()->toDateString(),
        'check_out_date' => Carbon::today()->toDateString(),
        'checked_in_at' => Carbon::yesterday(),
        'total_nights' => 1,
        'guests_count' => 1,
        'nightly_price' => 3500,
        'accommodation_total' => 3500,
        'total_amount' => 3500,
        'currency' => 'ETB',
        'status' => 'checked_in',
        'payment_status' => 'paid',
    ]);

    $response = $this->actingAs($this->receptionistUser)
        ->post(route('receptionist.check-out', $booking));

    $response->assertSessionHas('success');

    $booking->refresh();
    expect($booking->status)->toBe('checked_out')
        ->and($booking->checked_out_at)->not->toBeNull();
});

test('receptionist can register and instantly check in a walk-in guest', function () {
    $response = $this->actingAs($this->receptionistUser)
        ->post(route('receptionist.walk-in'), [
            'property_id' => $this->property->id,
            'guest_name' => 'Marta Walkin',
            'guest_phone' => '+251922334455',
            'guest_email' => 'marta.walkin@example.com',
            'guest_id_number' => 'ETH-PASSPORT-554433',
            'room_number' => 'Room 305',
            'check_in_date' => Carbon::today()->toDateString(),
            'check_out_date' => Carbon::today()->addDays(2)->toDateString(),
            'nightly_price' => 3500,
            'guests_count' => 1,
            'payment_method' => 'cash',
            'check_in_now' => 1,
            'payment_reference' => 'CASH-DRAWER-01',
        ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('bookings', [
        'property_id' => $this->property->id,
        'guest_name' => 'Marta Walkin',
        'room_number' => 'Room 305',
        'guest_id_number' => 'ETH-PASSPORT-554433',
        'total_nights' => 2,
        'total_amount' => 7000,
        'status' => 'checked_in',
        'payment_status' => 'paid',
    ]);

    $this->assertDatabaseHas('payments', [
        'payment_method' => 'cash',
        'amount' => 7000,
        'status' => 'confirmed',
    ]);
});

test('receptionist can manage guest service requests lifecycle', function () {
    $booking = Booking::create([
        'user_id' => $this->clientUser->id,
        'property_id' => $this->property->id,
        'room_number' => 'Room 201',
        'guest_name' => 'Abebe Guest',
        'guest_email' => 'abebe.guest@example.com',
        'guest_phone' => '+251911223344',
        'check_in_date' => Carbon::today()->toDateString(),
        'check_out_date' => Carbon::tomorrow()->toDateString(),
        'total_nights' => 1,
        'guests_count' => 1,
        'nightly_price' => 3500,
        'accommodation_total' => 3500,
        'total_amount' => 3500,
        'status' => 'checked_in',
    ]);

    // Store request
    $createResponse = $this->actingAs($this->receptionistUser)
        ->post(route('receptionist.service-requests.store'), [
            'booking_id' => $booking->id,
            'service_type' => 'room_cleaning',
            'priority' => 'urgent',
            'description' => 'Guest requested immediate afternoon housekeeping refresh.',
        ]);

    $createResponse->assertSessionHas('success');

    $request = GuestServiceRequest::where('booking_id', $booking->id)->first();
    expect($request)->not->toBeNull()
        ->and($request->service_type)->toBe('room_cleaning')
        ->and($request->priority)->toBe('urgent')
        ->and($request->status)->toBe('pending');

    // Update status to completed
    $updateResponse = $this->actingAs($this->receptionistUser)
        ->patch(route('receptionist.service-requests.update', $request), [
            'status' => 'completed',
            'response_notes' => 'Housekeeping completed by cleaner Hanna at 14:30.',
        ]);

    $updateResponse->assertSessionHas('success');

    $request->refresh();
    expect($request->status)->toBe('completed')
        ->and($request->response_notes)->toContain('Housekeeping completed');
});

test('receptionist can submit shift reconciliation and finance officer can verify it', function () {
    $response = $this->actingAs($this->receptionistUser)
        ->post(route('receptionist.shifts.store'), [
            'property_id' => $this->property->id,
            'shift_date' => Carbon::today()->toDateString(),
            'shift_type' => 'day',
            'cash_collected' => 7000,
            'telebirr_collected' => 3500,
            'cbe_collected' => 3500,
            'card_pos_collected' => 0,
            'checkins_count' => 4,
            'checkouts_count' => 2,
            'notes' => 'Smooth shift, all cash drawer envelopes counted and sealed.',
        ]);

    $response->assertSessionHas('success');

    $shift = FrontDeskShift::where('receptionist_user_id', $this->receptionistUser->id)->latest()->first();
    expect($shift)->not->toBeNull()
        ->and($shift->total_collected)->toBe(14000.0)
        ->and($shift->status)->toBe('submitted');

    // Finance Officer verifies shift
    $verifyResponse = $this->actingAs($this->financeUser)
        ->post(route('finance.shifts.verify', $shift), [
            'status' => 'verified_by_finance',
            'finance_notes' => 'Envelope received and bank deposit verified.',
        ]);

    $verifyResponse->assertSessionHas('success');

    $shift->refresh();
    expect($shift->status)->toBe('verified_by_finance')
        ->and($shift->finance_officer_id)->toBe($this->financeUser->id)
        ->and($shift->finance_verified_at)->not->toBeNull();
});

test('receptionist can view printable folio invoice', function () {
    $booking = Booking::create([
        'user_id' => $this->clientUser->id,
        'property_id' => $this->property->id,
        'room_number' => 'Suite 101',
        'guest_name' => 'Abebe Guest',
        'guest_email' => 'abebe.guest@example.com',
        'guest_phone' => '+251911223344',
        'check_in_date' => Carbon::today()->toDateString(),
        'check_out_date' => Carbon::today()->addDays(2)->toDateString(),
        'total_nights' => 2,
        'guests_count' => 2,
        'nightly_price' => 3500,
        'accommodation_total' => 7000,
        'total_amount' => 7000,
        'currency' => 'ETB',
        'status' => 'checked_in',
        'payment_status' => 'paid',
    ]);

    $response = $this->actingAs($this->receptionistUser)
        ->get(route('receptionist.bookings.folio', $booking));

    $response->assertStatus(200);
    $response->assertSee('Official Guest Folio');
    $response->assertSee('Suite 101');
    $response->assertSee('7,000.00');
});

test('receptionist can view reviews, maintenance tickets, communicate with guests, and view properties', function () {
    // 1. Create a review
    PropertyReview::create([
        'property_id' => $this->property->id,
        'user_id' => $this->clientUser->id,
        'rating' => 5,
        'cleanliness_rating' => 5,
        'accuracy_rating' => 5,
        'communication_rating' => 5,
        'location_rating' => 5,
        'value_rating' => 5,
        'review_title' => 'Fantastic Front Desk Service',
        'review_body' => 'Checking in was seamless and room was spotless.',
        'is_verified_stay' => true,
        'status' => 'published',
    ]);

    // 2. Create a maintenance ticket
    $ticket = MaintenanceTicket::create([
        'property_id' => $this->property->id,
        'user_id' => $this->clientUser->id,
        'title' => 'Bathroom AC leakage',
        'category' => 'plumbing',
        'priority' => 'high',
        'status' => 'open',
        'description' => 'Water drop in bathroom AC unit.',
    ]);

    // 3. Create an inquiry
    $inquiry = Inquiry::create([
        'name' => 'Dawit Guest',
        'email' => 'dawit@example.com',
        'phone' => '+251922334455',
        'subject' => 'Late check-out request',
        'message' => 'Can I request check-out at 14:00 tomorrow?',
        'status' => 'unread',
    ]);

    // Test GET /receptionist
    $response = $this->actingAs($this->receptionistUser)
        ->get(route('receptionist.dashboard'));

    $response->assertOk();
    $response->assertSee('Fantastic Front Desk Service');
    $response->assertSee('Bathroom AC leakage');
    $response->assertSee('Late check-out request');
    $response->assertSee('Dawit Guest');
    $response->assertSee('Guest Reviews Desk');
    $response->assertSee('Maintenance and Repairs');
    $response->assertSee('Guest Communication');
    $response->assertSee('Properties (Admin vs Agent)');

    // Test PATCH maintenance status
    $patchMnt = $this->actingAs($this->receptionistUser)
        ->patch(route('receptionist.maintenance.update', $ticket), [
            'status' => 'in_progress',
            'resolution_notes' => 'Plumber dispatched to room.',
        ]);
    $patchMnt->assertSessionHas('success');
    expect($ticket->fresh()->status)->toBe('in_progress');

    // Test PATCH inquiry reply status
    $patchInq = $this->actingAs($this->receptionistUser)
        ->patch(route('receptionist.inquiries.reply', $inquiry), [
            'status' => 'responded',
        ]);
    $patchInq->assertSessionHas('success');
    expect($inquiry->fresh()->status)->toBe('responded');
});

test('host or receptionist can create a new short-stay listing with prices, rules, and availability', function () {
    $response = $this->actingAs($this->receptionistUser)
        ->post(route('receptionist.stays.store'), [
            'title' => 'Bole Medhanealem Modern 2BR Penthouse',
            'property_category' => 'serviced_apartment',
            'city' => 'Addis Ababa',
            'address' => 'Rwanda Embassy Road, Bole',
            'price' => 4500,
            'cleaning_fee' => 600,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area' => 110,
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'minimum_stay' => 2,
            'maximum_stay' => 14,
            'is_available' => true,
            'available_from' => Carbon::today()->toDateString(),
            'available_to' => Carbon::today()->addMonths(3)->toDateString(),
            'house_rules' => "1. No indoor smoking.\n2. Quiet hours after 22:00.\n3. National ID required.",
            'description' => 'Beautiful high-floor serviced apartment with fast WiFi and full amenities.',
        ]);

    $response->assertSessionHas('success');

    $property = Property::where('title', 'Bole Medhanealem Modern 2BR Penthouse')->first();
    expect($property)->not->toBeNull();
    expect($property->type)->toBe('stay');
    expect($property->host_id)->toBe($this->receptionistUser->id);
    expect((float) $property->price)->toBe(4500.0);
    expect((float) $property->cleaning_fee)->toBe(600.0);
    expect($property->house_rules)->toContain('No indoor smoking');
    expect($property->is_available)->toBeTrue();
    expect($property->check_in_time)->toBe('14:00');
    expect($property->check_out_time)->toBe('11:00');

    // Matching short-stay listing is also created
    $listing = $property->listings()->where('listing_type', 'stay')->first();
    expect($listing)->not->toBeNull();
    expect((float) $listing->price)->toBe(4500.0);
    expect((float) $listing->cleaning_fee)->toBe(600.0);
    expect($listing->house_rules)->toContain('Quiet hours');
});

test('host or receptionist can update short-stay listing prices, house rules, and details', function () {
    $response = $this->actingAs($this->receptionistUser)
        ->put(route('receptionist.stays.update', $this->property), [
            'title' => 'Bole Grand Luxury Guest House Updated',
            'description' => 'Updated luxury stay description with breakfast included.',
            'price' => 5200,
            'cleaning_fee' => 750,
            'house_rules' => 'No pets allowed. Valid ID strictly required.',
            'check_in_time' => '15:00',
            'check_out_time' => '12:00',
            'is_available' => true,
            'minimum_stay' => 1,
            'maximum_stay' => 30,
        ]);

    $response->assertSessionHas('success');

    $this->property->refresh();
    expect($this->property->title)->toBe('Bole Grand Luxury Guest House Updated');
    expect((float) $this->property->price)->toBe(5200.0);
    expect((float) $this->property->cleaning_fee)->toBe(750.0);
    expect($this->property->house_rules)->toBe('No pets allowed. Valid ID strictly required.');
    expect($this->property->check_in_time)->toBe('15:00');
    expect($this->property->check_out_time)->toBe('12:00');
});

test('host or receptionist can define availability toggle and calendar window', function () {
    // Block availability
    $response = $this->actingAs($this->receptionistUser)
        ->put(route('receptionist.stays.availability', $this->property), [
            'is_available' => 0,
            'available_from' => Carbon::today()->addDays(5)->toDateString(),
            'available_to' => Carbon::today()->addDays(20)->toDateString(),
        ]);

    $response->assertSessionHas('success');

    $this->property->refresh();
    expect($this->property->is_available)->toBeFalse();
    expect($this->property->isAvailableForDates(Carbon::today(), Carbon::tomorrow()))->toBeFalse();

    // Re-enable availability
    $enableResponse = $this->actingAs($this->receptionistUser)
        ->put(route('receptionist.stays.availability', $this->property), [
            'is_available' => 1,
            'available_from' => Carbon::today()->toDateString(),
            'available_to' => Carbon::today()->addDays(30)->toDateString(),
        ]);

    $enableResponse->assertSessionHas('success');
    $this->property->refresh();
    expect($this->property->is_available)->toBeTrue();
});

test('host or receptionist can accept a pending booking reservation', function () {
    $booking = Booking::create([
        'user_id' => $this->clientUser->id,
        'property_id' => $this->property->id,
        'guest_name' => 'Almaz Guest',
        'guest_email' => 'almaz@example.com',
        'guest_phone' => '+251911998877',
        'check_in_date' => Carbon::today()->addDays(2)->toDateString(),
        'check_out_date' => Carbon::today()->addDays(4)->toDateString(),
        'total_nights' => 2,
        'guests_count' => 1,
        'nightly_price' => 3500,
        'accommodation_total' => 7000,
        'total_amount' => 7000,
        'currency' => 'ETB',
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    expect($booking->status)->toBe('pending');

    $response = $this->actingAs($this->receptionistUser)
        ->post(route('receptionist.bookings.accept', $booking), [
            'host_notes' => 'Reservation confirmed by front desk host.',
        ]);

    $response->assertSessionHas('success');
    expect($booking->fresh()->status)->toBe('confirmed');
    expect($booking->fresh()->host_notes)->toBe('Reservation confirmed by front desk host.');
});

test('host or receptionist can manage cleaning by dispatching a room turnover request', function () {
    $response = $this->actingAs($this->receptionistUser)
        ->post(route('receptionist.cleaning.store', $this->property), [
            'room_number' => 'Suite 302',
            'priority' => 'urgent',
            'description' => 'Full turnaround sanitization, replace duvet cover, restock bath towels and soap.',
        ]);

    $response->assertSessionHas('success');

    $request = GuestServiceRequest::where('room_number', 'Suite 302')->first();
    expect($request)->not->toBeNull();
    expect($request->service_type)->toBe('room_cleaning');
    expect($request->priority)->toBe('urgent');
    expect($request->status)->toBe('pending');
    expect($request->handled_by)->toBe($this->receptionistUser->id);
    expect($request->property_id)->toBe($this->property->id);
});

test('host or receptionist can reply to guest reviews', function () {
    $review = PropertyReview::create([
        'property_id' => $this->property->id,
        'user_id' => $this->clientUser->id,
        'rating' => 5,
        'cleanliness_rating' => 5,
        'accuracy_rating' => 5,
        'communication_rating' => 5,
        'location_rating' => 5,
        'value_rating' => 5,
        'review_title' => 'Unbeatable hospitality',
        'review_body' => 'Super clean rooms and the receptionist staff was remarkably attentive.',
        'status' => 'published',
        'is_verified_stay' => true,
    ]);

    expect($review->host_reply)->toBeNull();

    $response = $this->actingAs($this->receptionistUser)
        ->post(route('receptionist.reviews.reply', $review), [
            'host_reply' => 'Thank you Abebe! It was our pleasure hosting you at Bole Grand.',
        ]);

    $response->assertSessionHas('success');

    $review->refresh();
    expect($review->host_reply)->toBe('Thank you Abebe! It was our pleasure hosting you at Bole Grand.');
    expect($review->host_replied_at)->not->toBeNull();
});

test('receptionist dashboard renders host stay desk, cleaning dispatch, and review reply forms', function () {
    $booking = Booking::create([
        'user_id' => $this->clientUser->id,
        'property_id' => $this->property->id,
        'guest_name' => 'Abebe Guest',
        'guest_email' => 'abebe.guest@example.com',
        'guest_phone' => '+251911223344',
        'check_in_date' => Carbon::today()->toDateString(),
        'check_out_date' => Carbon::tomorrow()->toDateString(),
        'total_nights' => 1,
        'guests_count' => 1,
        'nightly_price' => 3500,
        'accommodation_total' => 3500,
        'total_amount' => 3500,
        'currency' => 'ETB',
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    PropertyReview::create([
        'property_id' => $this->property->id,
        'user_id' => $this->clientUser->id,
        'rating' => 5,
        'review_title' => 'Great experience',
        'review_body' => 'Host was responsive and place was spotless.',
        'status' => 'published',
    ]);

    $response = $this->actingAs($this->receptionistUser)
        ->get(route('receptionist.dashboard'));

    $response->assertOk();
    $response->assertSee('Host / Stay Listings');
    $response->assertSee('Host Short-Stay Listings, Availability &amp; Rules', false);
    $response->assertSee('Schedule Turnover / Room Cleaning');
    $response->assertSee('Post Host Reply');
    $response->assertSee('✓ Accept');
});

test('receptionist dashboard displays in admin dashboard layout with custom color palette and controls', function () {
    $response = $this->actingAs($this->receptionistUser)
        ->get(route('receptionist.dashboard'));

    $response->assertOk();
    // Default theme matches other dashboards (Highland Emerald)
    $response->assertSee('--theme-primary: #1d3c34', false);
    $response->assertSee('--theme-primary-dark: #102b25', false);
    $response->assertSee('--theme-site-bg: #f6f1e8', false);

    // Admin Dashboard Header anatomy
    $response->assertSee('Front Desk Control Panel');
    $response->assertSee('Receptionist Desk &amp; Guest Management', false);
    // Palette Switcher in sidebar navigation bar
    $response->assertSee('Desk Color');
    $response->assertSee('palette-switcher-btn');
    $response->assertSee('Highland Emerald');
    $response->assertSee('Oak Wood');
    $response->assertSee('Soft Cream');
    $response->assertSee('Sapphire Navy');
    $response->assertSee('Bordeaux Velvet');
    $response->assertSee('Obsidian Slate');

    // Housekeeping quick presets visibility
    $response->assertSee('Full Turnover');
    $response->assertSee('Linen Refresh');
    $response->assertSee('Deep Clean');

    // Notifications & alerts bell dropdown
    $response->assertSee('dashboard-messages-icon');
    $response->assertSee('Front Desk Alerts &amp; Activities', false);

    // Admin-style metric stat cards
    $response->assertSee('In-House Guests');
    $response->assertSee('Arrivals');
    $response->assertSee('Departures');
    $response->assertSee("Today's Collections", false);

    // Admin-style 2-column split overview
    $response->assertSee("Today's Expected Arrivals (Check-Ins)", false);
    $response->assertSee('Currently In-House (Occupied Rooms)', false);
    $response->assertSee('Full Arrivals Board &rarr;', false);
    $response->assertSee('Full In-House Board &rarr;', false);

    // Profile & Logout (logout is in the sidebar nav bottom, not in top action bar near profile)
    $response->assertSee('Staff Profile');
    $response->assertSee(route('logout'));
});

test('host or receptionist can upload image when creating a short-stay listing and image displays on dashboard', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('penthouse_suite.jpg', 800, 600);

    $response = $this->actingAs($this->receptionistUser)
        ->post(route('receptionist.stays.store'), [
            'title' => 'Bole Atlantic Vista Guesthouse',
            'city' => 'Addis Ababa',
            'address' => 'Bole Medhanealem, Cameroon St',
            'price' => 4800,
            'cleaning_fee' => 500,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area' => 85,
            'property_category' => 'guest_house',
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'is_available' => 1,
            'house_rules' => 'No smoking. Valid ID required.',
            'description' => 'Luxury stay in central Bole.',
            'image' => $file,
        ]);

    $response->assertSessionHas('success');

    $property = Property::where('title', 'Bole Atlantic Vista Guesthouse')->first();
    expect($property)->not->toBeNull();
    expect($property->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($property->image_path);
    expect($property->images()->count())->toBe(1);

    // Dashboard displays the short-stay property image
    $dashboardResponse = $this->actingAs($this->receptionistUser)->get(route('receptionist.dashboard'));
    $dashboardResponse->assertOk();
    $dashboardResponse->assertSee($property->primary_image_url);
});
