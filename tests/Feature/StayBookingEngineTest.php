<?php

use App\Models\Agent;
use App\Models\Booking;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->agentUser = User::create([
        'name' => 'Stay Host Agent',
        'email' => 'host.agent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->guestHouseCategory = 'guest_house';
    $this->condominiumCategory = 'condominium';

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Bole Stays Hospitality',
        'email' => 'bolestays@example.com',
        'phone' => '+251 91 199 8877',
        'is_active' => true,
    ]);

    $this->adminUser = User::create([
        'name' => 'System Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->property = Property::create([
        'title' => 'Bole Medhanealem Luxury Penthouse',
        'slug' => 'bole-medhanealem-luxury-penthouse',
        'description' => 'Spacious penthouse perfect for expatriates and short stays.',
        'price' => 7500.00,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 220,
        'type' => 'rent',
        'property_category' => 'apartment',
        'city' => 'Addis Ababa',
        'address' => 'Near Edna Mall, Bole Medhanealem',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $this->agent->id,
    ]);

    $this->stayListing = Listing::create([
        'property_id' => $this->property->id,
        'listing_type' => 'stay',
        'title' => 'Bole Medhanealem Luxury Penthouse (Short Stay)',
        'price' => 6000.00,
        'currency' => 'ETB',
        'pricing_period' => 'nightly',
        'cleaning_fee' => 750.00,
        'deposit_amount' => 2000.00,
        'minimum_stay' => 2,
        'status' => 'published',
    ]);
});

test('admin can create guest house and condominium listings with expanded property categories', function () {
    $payload = [
        'title' => 'Boutique Guest House in Old Airport',
        'description' => 'A premium guest house ideal for short-stay visitors.',
        'price' => 4200,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 150,
        'type' => 'rent',
        'category' => 'guest_house',
        'status' => 'published',
        'city' => 'Addis Ababa',
        'address' => 'Old Airport Road',
        'featured' => false,
    ];

    $response = $this->actingAs($this->adminUser)->post(route('dashboard.properties.store'), $payload);

    $response->assertRedirect();
    $this->assertDatabaseHas('properties', [
        'title' => 'Boutique Guest House in Old Airport',
        'property_category' => 'guest_house',
    ]);

    $condoPayload = array_merge($payload, [
        'title' => 'Skyline Condominium Residence',
        'category' => 'condominium',
        'address' => 'Megenagna',
    ]);

    $condoResponse = $this->actingAs($this->adminUser)->post(route('dashboard.properties.store'), $condoPayload);

    $condoResponse->assertRedirect();
    $this->assertDatabaseHas('properties', [
        'title' => 'Skyline Condominium Residence',
        'property_category' => 'condominium',
    ]);
});

test('guests are prompted to create an account before submitting a property order request', function () {
    $property = Property::create([
        'title' => 'Guest Access Restricted Property',
        'slug' => 'guest-access-restricted-property',
        'description' => 'Private property that requires a signed-in customer to submit an offer.',
        'price' => 8500,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 240,
        'type' => 'sale',
        'property_category' => 'villa',
        'city' => 'Addis Ababa',
        'address' => 'Bole',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $this->agent->id,
    ]);

    $response = $this->post(route('orders.store', $property), [
        'name' => 'Guest Buyer',
        'email' => 'guest@example.com',
        'phone' => '+251 911 123 456',
        'request_kind' => 'order',
        'message' => 'I want to reserve this property.',
    ]);

    $response->assertRedirect(route('register'))
        ->assertSessionHas('signup_prompt', 'Register / Login to continue: Create a client account or sign in before submitting a request for this property.');

    $registerPage = $this->get(route('register'));
    $registerPage->assertSee('Register / Login to continue: Create a client account or sign in before submitting a request for this property.')
        ->assertSee(route('login'));

    $registration = $this->post(route('register.store'), [
        'name' => 'Guest Buyer',
        'email' => 'guest@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $registration->assertRedirect(route('properties.show', $property->slug));
});

test('guests are prompted to create an account before reserving a stay', function () {
    $response = $this->post(route('stays.book', $this->property));

    $response->assertRedirect(route('register'))
        ->assertSessionHas('signup_prompt', 'Register / Login to continue: Create a client account or sign in before reserving this stay.');

    expect(Booking::count())->toBe(0);
});

test('public stays page displays short-stay listings and search filters', function () {
    $response = $this->get(route('stays.index'));

    $response->assertStatus(200);
    $response->assertSee('Short-Stay Accommodations');
    $response->assertSee('Bole Medhanealem Luxury Penthouse');
    $response->assertSee('ETB 6,000');
    $response->assertSee('/ night');
    $response->assertSee('Reserve Stay');
});

test('stays calculation endpoint accurately computes night rates, cleaning fee and service fees', function () {
    $checkIn = Carbon::now()->addDays(5)->format('Y-m-d');
    $checkOut = Carbon::now()->addDays(9)->format('Y-m-d'); // 4 nights

    $client = User::create([
        'name' => 'Stay Calculator',
        'email' => 'stay.calculator@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $response = $this->actingAs($client)->postJson(route('stays.calculate', $this->property), [
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'guests' => 2,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'available' => true,
        'nights' => 4,
        'nightly_price' => 6000,
        'accommodation_total' => 24000,
        'cleaning_fee' => 750,
        'service_fee' => 1200,
        'total_amount' => 25950,
        'currency' => 'ETB',
    ]);
});

test('guests must sign in before calculating stay booking details', function () {
    $response = $this->postJson(route('stays.calculate', $this->property), [
        'check_in' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'check_out' => Carbon::now()->addDays(9)->format('Y-m-d'),
        'guests' => 2,
    ]);

    $response->assertUnauthorized()
        ->assertJson([
            'auth_required' => true,
            'message' => 'Sign in before checking stay availability.',
            'login_url' => route('login'),
            'register_url' => route('register'),
        ]);
});

test('user can successfully book a short stay with unique reservation reference', function () {
    $client = User::create([
        'name' => 'Dawit Haile',
        'email' => 'dawit@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $checkIn = Carbon::now()->addDays(10)->format('Y-m-d');
    $checkOut = Carbon::now()->addDays(13)->format('Y-m-d'); // 3 nights

    $payload = [
        'check_in_date' => $checkIn,
        'check_out_date' => $checkOut,
        'guests_count' => 2,
        'guest_name' => 'Dawit Haile',
        'guest_email' => 'dawit@example.com',
        'guest_phone' => '+251 91 234 5678',
        'special_requests' => 'Late airport check-in around 10 PM please.',
    ];

    $response = $this->actingAs($client)->post(route('stays.book', $this->property), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('stay_booking_success');

    $booking = Booking::where('property_id', $this->property->id)->first();
    expect($booking)->not->toBeNull()
        ->and($booking->booking_reference)->toStartWith('STAY-AA-')
        ->and($booking->total_nights)->toBe(3)
        ->and((float) $booking->nightly_price)->toBe(6000.0)
        ->and((float) $booking->accommodation_total)->toBe(18000.0)
        ->and((float) $booking->cleaning_fee)->toBe(750.0)
        ->and((float) $booking->service_fee)->toBe(900.0) // 5% of 18000
        ->and((float) $booking->total_amount)->toBe(19650.0)
        // Reservations stay pending until the finance desk clears the payment.
        ->and($booking->status)->toBe('pending')
        ->and($booking->payment_status)->toBe('unpaid')
        ->and($booking->user_id)->toBe($client->id)
        ->and($booking->guest_name)->toBe('Dawit Haile');
});

test('stays booking engine prevents double booking on overlapping dates', function () {
    $existingCheckIn = Carbon::now()->addDays(15)->format('Y-m-d');
    $existingCheckOut = Carbon::now()->addDays(20)->format('Y-m-d');

    Booking::create([
        'booking_reference' => 'STAY-AA-202610-TEST01',
        'property_id' => $this->property->id,
        'listing_id' => $this->stayListing->id,
        'guest_name' => 'Existing Guest',
        'guest_email' => 'existing@example.com',
        'guest_phone' => '+251 91 000 1111',
        'check_in_date' => $existingCheckIn,
        'check_out_date' => $existingCheckOut,
        'total_nights' => 5,
        'guests_count' => 2,
        'nightly_price' => 6000.00,
        'accommodation_total' => 30000.00,
        'cleaning_fee' => 750.00,
        'service_fee' => 1500.00,
        'total_amount' => 32250.00,
        'currency' => 'ETB',
        'status' => 'confirmed',
    ]);

    // Attempt overlapping reservation (starts inside existing booking)
    $conflictPayload = [
        'check_in_date' => Carbon::now()->addDays(17)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(22)->format('Y-m-d'),
        'guests_count' => 2,
        'guest_name' => 'Conflicting Guest',
        'guest_email' => 'conflict@example.com',
        'guest_phone' => '+251 91 222 3333',
    ];

    $client = User::create([
        'name' => 'Conflicting Guest',
        'email' => 'conflict@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $response = $this->actingAs($client)->post(route('stays.book', $this->property), $conflictPayload);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['check_in_date']);

    // Check availability helper method
    expect($this->property->isAvailableForDates(
        Carbon::now()->addDays(17)->format('Y-m-d'),
        Carbon::now()->addDays(22)->format('Y-m-d')
    ))->toBeFalse();

    // Adjacent non-overlapping dates must be available (check-in on checkout date)
    expect($this->property->isAvailableForDates(
        $existingCheckOut,
        Carbon::now()->addDays(25)->format('Y-m-d')
    ))->toBeTrue();
});

test('admin can view stay bookings and update their status in dashboard', function () {
    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-202610-ADMIN1',
        'property_id' => $this->property->id,
        'listing_id' => $this->stayListing->id,
        'guest_name' => 'VIP Diplomat Guest',
        'guest_email' => 'diplomat@example.com',
        'guest_phone' => '+251 91 777 8888',
        'check_in_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(6)->format('Y-m-d'),
        'total_nights' => 4,
        'guests_count' => 1,
        'nightly_price' => 6000.00,
        'accommodation_total' => 24000.00,
        'cleaning_fee' => 750.00,
        'service_fee' => 1200.00,
        'total_amount' => 25950.00,
        'currency' => 'ETB',
        'status' => 'confirmed',
    ]);

    // Admin views dashboard
    $response = $this->actingAs($this->adminUser)->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Stay Bookings');
    $response->assertSee('Stay Platform');
    $response->assertDontSee('Module 2');
    $response->assertSee('STAY-AA-202610-ADMIN1');
    $response->assertSee('VIP Diplomat Guest');

    // Admin updates status to checked_in
    $updateResponse = $this->actingAs($this->adminUser)->put(route('bookings.update-status', $booking), [
        'status' => 'checked_in',
    ]);

    $updateResponse->assertRedirect();
    $updateResponse->assertSessionHas('success');

    expect($booking->fresh()->status)->toBe('checked_in');

    $completeResponse = $this->actingAs($this->adminUser)->put(route('bookings.update-status', $booking), [
        'status' => 'completed',
    ]);

    $completeResponse->assertRedirect();
    expect($booking->fresh()->status)->toBe('completed')
        ->and($this->property->isAvailableForDates(
            Carbon::now()->addDays(2)->format('Y-m-d'),
            Carbon::now()->addDays(6)->format('Y-m-d')
        ))->toBeTrue();

    $dashboard = $this->actingAs($this->adminUser)->get(route('dashboard'));
    $dashboard->assertDontSee('VIP Diplomat Guest');
});

test('dashboard completes expired stay bookings and removes guests from the active list', function () {
    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-202610-EXPIRED1',
        'property_id' => $this->property->id,
        'listing_id' => $this->stayListing->id,
        'guest_name' => 'Departed Guest',
        'guest_email' => 'departed@example.com',
        'guest_phone' => '+251 91 111 2233',
        'check_in_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        'check_out_date' => Carbon::yesterday()->format('Y-m-d'),
        'total_nights' => 4,
        'guests_count' => 1,
        'nightly_price' => 6000.00,
        'accommodation_total' => 24000.00,
        'cleaning_fee' => 750.00,
        'service_fee' => 1200.00,
        'total_amount' => 25950.00,
        'currency' => 'ETB',
        'status' => 'checked_in',
    ]);

    $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

    $response->assertOk()
        ->assertDontSee($booking->booking_reference)
        ->assertDontSee('Departed Guest');

    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'status' => 'completed',
    ]);
});

test('admin can delete a stay booking and clients cannot', function () {
    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-202610-DELETE1',
        'property_id' => $this->property->id,
        'listing_id' => $this->stayListing->id,
        'guest_name' => 'Guest To Remove',
        'guest_email' => 'remove@example.com',
        'guest_phone' => '+251 91 111 2233',
        'check_in_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
        'total_nights' => 2,
        'guests_count' => 1,
        'nightly_price' => 6000.00,
        'accommodation_total' => 12000.00,
        'cleaning_fee' => 750.00,
        'service_fee' => 600.00,
        'total_amount' => 13350.00,
        'currency' => 'ETB',
        'status' => 'confirmed',
    ]);
    $client = User::create([
        'name' => 'Stay Client',
        'email' => 'stay.client@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $forbiddenResponse = $this->actingAs($client)->delete(route('bookings.destroy', $booking));

    $forbiddenResponse->assertForbidden();
    $this->assertDatabaseHas('bookings', ['id' => $booking->id]);

    $deleteResponse = $this->actingAs($this->adminUser)->delete(route('bookings.destroy', $booking));

    $deleteResponse->assertRedirect()->assertSessionHas('success');
    $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
});

test('client can delete their own stay booking', function () {
    $client = User::create([
        'name' => 'Booking Owner',
        'email' => 'booking.owner@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);
    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-202610-OWNER1',
        'property_id' => $this->property->id,
        'listing_id' => $this->stayListing->id,
        'user_id' => $client->id,
        'guest_name' => 'Booking Owner',
        'guest_email' => $client->email,
        'guest_phone' => '+251 91 111 2233',
        'check_in_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
        'total_nights' => 2,
        'guests_count' => 1,
        'nightly_price' => 6000.00,
        'accommodation_total' => 12000.00,
        'cleaning_fee' => 750.00,
        'service_fee' => 600.00,
        'total_amount' => 13350.00,
        'currency' => 'ETB',
        'status' => 'confirmed',
    ]);

    $portal = $this->actingAs($client)->get(route('client.portal'));
    $portal->assertOk()
        ->assertSee(route('bookings.destroy', $booking), false);

    $response = $this->actingAs($client)->delete(route('bookings.destroy', $booking));

    $response->assertRedirect();
    $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
});

test('assigned agent can view and delete a stay booking for their property', function () {
    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-202610-AGENT1',
        'property_id' => $this->property->id,
        'listing_id' => $this->stayListing->id,
        'guest_name' => 'Agent Guest',
        'guest_email' => 'agent.guest@example.com',
        'guest_phone' => '+251 91 111 2233',
        'check_in_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
        'total_nights' => 2,
        'guests_count' => 1,
        'nightly_price' => 6000.00,
        'accommodation_total' => 12000.00,
        'cleaning_fee' => 750.00,
        'service_fee' => 600.00,
        'total_amount' => 13350.00,
        'currency' => 'ETB',
        'status' => 'confirmed',
    ]);

    $portal = $this->actingAs($this->agentUser)->get(route('agent.portal'));
    $portal->assertOk()
        ->assertSee($booking->booking_reference)
        ->assertSee(route('bookings.destroy', $booking), false);

    $response = $this->actingAs($this->agentUser)->delete(route('bookings.destroy', $booking));

    $response->assertRedirect();
    $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
});

test('agent stay bookings section shows the payment state and receipt link', function () {
    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-202610-AGENT2',
        'property_id' => $this->property->id,
        'listing_id' => $this->stayListing->id,
        'guest_name' => 'Paid Guest',
        'guest_email' => 'paid.guest@example.com',
        'guest_phone' => '+251 91 111 2244',
        'check_in_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(6)->format('Y-m-d'),
        'total_nights' => 3,
        'guests_count' => 2,
        'nightly_price' => 6000.00,
        'accommodation_total' => 18000.00,
        'cleaning_fee' => 750.00,
        'service_fee' => 900.00,
        'total_amount' => 19650.00,
        'currency' => 'ETB',
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ]);

    $payment = Payment::create([
        'payable_type' => Booking::class,
        'payable_id' => $booking->id,
        'property_id' => $this->property->id,
        'user_id' => $this->agentUser->id,
        'payer_name' => 'Paid Guest',
        'payer_email' => 'paid.guest@example.com',
        'payment_type' => 'stay_booking',
        'payment_method' => 'telebirr',
        'amount' => 19650.00,
        'platform_fee' => 900.00,
        'status' => 'completed',
    ]);

    $this->actingAs($this->agentUser)
        ->get(route('agent.portal'))
        ->assertOk()
        ->assertSee('Stay Bookings on My Listings')
        ->assertSee('STAY-AA-202610-AGENT2')
        ->assertSee('Payment Confirmed')
        ->assertSee(route('payments.receipt', $payment->transaction_number), false);
});

test('agent stay bookings section excludes other agents bookings', function () {
    $otherAgentUser = User::create([
        'name' => 'Other Stay Host',
        'email' => 'other.host@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $otherAgent = Agent::create([
        'user_id' => $otherAgentUser->id,
        'name' => 'Other Host Agency',
        'email' => 'other.host@example.com',
        'phone' => '+251 91 555 6677',
        'is_active' => true,
    ]);

    $otherProperty = Property::create([
        'property_code' => 'ETH-AA-VIL-00000077',
        'title' => 'Other Agent Guest Villa',
        'slug' => 'other-agent-guest-villa',
        'price' => 9000.00,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 120,
        'type' => 'rent',
        'property_category' => 'guest_house',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'agent_id' => $otherAgent->id,
    ]);

    Booking::create([
        'booking_reference' => 'STAY-AA-202610-OTHER1',
        'property_id' => $otherProperty->id,
        'listing_id' => $this->stayListing->id,
        'guest_name' => 'Someone Else Guest',
        'guest_email' => 'someone.else@example.com',
        'guest_phone' => '+251 91 111 2255',
        'check_in_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
        'total_nights' => 2,
        'guests_count' => 1,
        'nightly_price' => 6000.00,
        'accommodation_total' => 12000.00,
        'cleaning_fee' => 750.00,
        'service_fee' => 600.00,
        'total_amount' => 13350.00,
        'currency' => 'ETB',
        'status' => 'confirmed',
    ]);

    $this->actingAs($this->agentUser)
        ->get(route('agent.portal'))
        ->assertOk()
        ->assertDontSee('STAY-AA-202610-OTHER1');
});

test('bank transfer stay payment waits for finance confirmation before confirming the reservation', function () {
    $client = User::create([
        'name' => 'Stay Guest Hanna',
        'email' => 'hanna.stay@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $financeOfficer = User::create([
        'name' => 'Finance Officer Meron',
        'email' => 'meron.finance@example.com',
        'password' => Hash::make('password123'),
        'role' => 'finance_officer',
    ]);

    $this->actingAs($client)->post(route('stays.book', $this->property), [
        'check_in_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(22)->format('Y-m-d'),
        'guests_count' => 2,
        'guest_name' => 'Hanna Stay Guest',
        'guest_email' => 'hanna.stay@example.com',
        'guest_phone' => '+251 91 555 7788',
    ])->assertRedirect();

    $booking = Booking::where('property_id', $this->property->id)->firstOrFail();
    $totalAmount = (float) $booking->total_amount;

    $this->actingAs($client)->post(route('bookings.pay', $booking), [
        'payment_method' => 'bank_transfer',
        'bank_name' => 'Commercial Bank of Ethiopia',
        'bank_transaction_reference' => 'FT26001STAYA',
        'notes' => 'CBE transfer slip uploaded for the stay.',
    ])->assertRedirect();

    $payment = Payment::where('payable_type', Booking::class)->where('payable_id', $booking->id)->firstOrFail();

    // The amount is derived from the reservation, never trusted from the request.
    expect($payment->status)->toBe('pending')
        ->and((float) $payment->amount)->toBe($totalAmount)
        ->and((float) $payment->platform_fee)->toBe((float) $booking->service_fee)
        ->and($booking->fresh()->status)->toBe('pending')
        ->and($booking->fresh()->payment_status)->toBe('awaiting_confirmation');

    // Nothing hits the ledger until finance clears the transaction.
    $this->assertDatabaseMissing('financial_ledgers', ['payment_id' => $payment->id]);

    // The finance desk surfaces it in the confirmation queue.
    $this->actingAs($financeOfficer)->get(route('finance.index'))
        ->assertOk()
        ->assertSee('Payments Awaiting Confirmation')
        ->assertSee($payment->transaction_number)
        ->assertSee($booking->booking_reference)
        ->assertSee('FT26001STAYA');

    $this->actingAs($financeOfficer)->put(route('payments.verify', $payment), [
        'admin_notes' => 'Slip matched the bank statement.',
    ])->assertRedirect();

    expect($payment->fresh()->status)->toBe('completed')
        ->and($payment->fresh()->verified_by)->toBe($financeOfficer->id)
        ->and($booking->fresh()->status)->toBe('confirmed')
        ->and($booking->fresh()->payment_status)->toBe('paid');

    $this->assertDatabaseHas('financial_ledgers', [
        'payment_id' => $payment->id,
        'category' => 'booking_revenue',
        'entry_type' => 'credit',
    ]);

    // The guest gets an in-app notification plus the receipt page.
    $notification = $client->notifications()->first();
    expect($notification)->not->toBeNull()
        ->and($notification->data['status'])->toBe('completed')
        ->and($notification->data['url'])->toBe(route('payments.receipt', $payment->transaction_number));

    $this->actingAs($client)->get(route('payments.receipt', $payment->transaction_number))
        ->assertOk()
        ->assertSee($payment->transaction_number)
        ->assertSee('Hanna Stay Guest');
});

test('rejected stay payment reopens the reservation for another attempt', function () {
    $client = User::create([
        'name' => 'Stay Guest Yonas',
        'email' => 'yonas.stay@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $financeOfficer = User::create([
        'name' => 'Finance Officer Meron',
        'email' => 'meron.finance@example.com',
        'password' => Hash::make('password123'),
        'role' => 'finance_officer',
    ]);

    $this->actingAs($client)->post(route('stays.book', $this->property), [
        'check_in_date' => Carbon::now()->addDays(25)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(27)->format('Y-m-d'),
        'guests_count' => 1,
        'guest_name' => 'Yonas Stay Guest',
        'guest_email' => 'yonas.stay@example.com',
        'guest_phone' => '+251 91 222 3311',
    ])->assertRedirect();

    $booking = Booking::where('property_id', $this->property->id)->firstOrFail();

    $this->actingAs($client)->post(route('bookings.pay', $booking), [
        'payment_method' => 'bank_transfer',
        'bank_name' => 'Dashen Bank',
        'bank_transaction_reference' => 'DB-REJECT-0001',
    ])->assertRedirect();

    $payment = Payment::where('payable_type', Booking::class)->where('payable_id', $booking->id)->firstOrFail();

    $this->actingAs($financeOfficer)->put(route('payments.reject', $payment), [
        'admin_notes' => 'Slip amount does not match the reservation total.',
    ])->assertRedirect();

    expect($payment->fresh()->status)->toBe('failed')
        ->and($booking->fresh()->payment_status)->toBe('rejected')
        ->and($booking->fresh()->status)->toBe('pending');

    // The guest may submit a corrected payment against the same reservation.
    $retry = $this->actingAs($client)->post(route('bookings.pay', $booking), [
        'payment_method' => 'bank_transfer',
        'bank_name' => 'Dashen Bank',
        'bank_transaction_reference' => 'DB-RETRY-0002',
    ]);

    $retry->assertRedirect();
    expect($booking->fresh()->payment_status)->toBe('awaiting_confirmation')
        ->and(Payment::where('payable_id', $booking->id)->count())->toBe(2);
});

test('a guest cannot pay for a reservation that is not theirs', function () {
    $owner = User::create([
        'name' => 'Stay Owner Selam',
        'email' => 'selam.stay@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $stranger = User::create([
        'name' => 'Stranger Bekele',
        'email' => 'bekele.stranger@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $booking = Booking::create([
        'booking_reference' => 'STAY-AA-202610-OWNER1',
        'property_id' => $this->property->id,
        'listing_id' => $this->stayListing->id,
        'user_id' => $owner->id,
        'guest_name' => 'Selam Stay Owner',
        'guest_email' => 'selam.stay@example.com',
        'guest_phone' => '+251 91 444 5566',
        'check_in_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
        'check_out_date' => Carbon::now()->addDays(32)->format('Y-m-d'),
        'total_nights' => 2,
        'guests_count' => 1,
        'nightly_price' => 6000.00,
        'accommodation_total' => 12000.00,
        'cleaning_fee' => 750.00,
        'service_fee' => 600.00,
        'total_amount' => 13350.00,
        'currency' => 'ETB',
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    $this->actingAs($stranger)->post(route('bookings.pay', $booking), [
        'payment_method' => 'telebirr',
        'bank_transaction_reference' => 'TB-STOLEN-01',
    ])->assertForbidden();

    $this->assertDatabaseCount('payments', 0);
    expect($booking->fresh()->payment_status)->toBe('unpaid');
});

test('property calculation applies customized cleaning fee and customized service fee', function () {
    $hotel = Property::create([
        'title' => 'Bole Grand Luxury Hotel Suite',
        'slug' => 'bole-grand-luxury-hotel-suite',
        'description' => 'Five-star hotel suite with full amenities.',
        'price' => 5000.00,
        'cleaning_fee' => 300.00,
        'service_fee' => 150.00,
        'bedrooms' => 1,
        'bathrooms' => 1,
        'area' => 60,
        'type' => 'stay',
        'property_category' => 'hotel',
        'city' => 'Addis Ababa',
        'address' => 'Bole Road',
        'status' => 'published',
        'is_active' => true,
    ]);

    expect($hotel->isShortStay())->toBeTrue();
    expect($hotel->getCleaningFee())->toBe(300.00);
    expect($hotel->getCustomServiceFee())->toBe(150.00);

    $client = User::create([
        'name' => 'Custom Fee Guest',
        'email' => 'custom.fee.guest@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $checkIn = Carbon::now()->addDays(5)->format('Y-m-d');
    $checkOut = Carbon::now()->addDays(7)->format('Y-m-d'); // 2 nights

    $response = $this->actingAs($client)->postJson(route('stays.calculate', $hotel), [
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'guests' => 1,
    ]);

    $response->assertOk();
    $response->assertJson([
        'nights' => 2,
        'nightly_price' => 5000,
        'accommodation_total' => 10000,
        'cleaning_fee' => 300,
        'service_fee' => 150,
        'total_amount' => 10450, // 10000 + 300 + 150
    ]);
});

test('property details only renders Book Stay action button and widget for hotels and guest houses / short-stays', function () {
    $hotel = Property::create([
        'title' => 'Kazanchis Executive Hotel',
        'slug' => 'kazanchis-executive-hotel',
        'description' => 'Centrally located business hotel.',
        'price' => 4000.00,
        'bedrooms' => 1,
        'bathrooms' => 1,
        'type' => 'stay',
        'property_category' => 'hotel',
        'city' => 'Addis Ababa',
        'status' => 'published',
        'is_active' => true,
    ]);

    $guestHouse = Property::create([
        'title' => 'Old Airport Serene Guest House',
        'slug' => 'old-airport-serene-guest-house',
        'description' => 'Cozy private guest house.',
        'price' => 3500.00,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'type' => 'stay',
        'property_category' => 'guest_house',
        'city' => 'Addis Ababa',
        'status' => 'published',
        'is_active' => true,
    ]);

    $regularSaleVilla = Property::create([
        'title' => 'CMC Residential Villa for Sale',
        'slug' => 'cmc-residential-villa-for-sale',
        'description' => 'Spacious 4 bedroom family villa for purchase.',
        'price' => 35000000.00,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'type' => 'sale',
        'property_category' => 'villa',
        'city' => 'Addis Ababa',
        'status' => 'published',
        'is_active' => true,
    ]);

    expect($hotel->isShortStay())->toBeTrue();
    expect($guestHouse->isShortStay())->toBeTrue();
    expect($regularSaleVilla->isShortStay())->toBeFalse();

    // Hotel details shows Book Stay and stay reservation studio
    $hotelResponse = $this->get(route('properties.show', $hotel->slug));
    $hotelResponse->assertOk();
    $hotelResponse->assertSee('Book Stay');
    $hotelResponse->assertSee('stay-reservation-studio');

    // Guest House details shows Book Stay and stay reservation studio
    $guestHouseResponse = $this->get(route('properties.show', $guestHouse->slug));
    $guestHouseResponse->assertOk();
    $guestHouseResponse->assertSee('Book Stay');
    $guestHouseResponse->assertSee('stay-reservation-studio');

    // Regular sale villa details DOES NOT show Book Stay button or stay reservation studio
    $villaResponse = $this->get(route('properties.show', $regularSaleVilla->slug));
    $villaResponse->assertOk();
    $villaResponse->assertDontSee('Book Stay');
    $villaResponse->assertDontSee('id="stay-reservation-studio"', false);
});
