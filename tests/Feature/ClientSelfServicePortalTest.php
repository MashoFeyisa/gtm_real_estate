<?php

use App\Models\Agent;
use App\Models\Booking;
use App\Models\GuestServiceRequest;
use App\Models\Lease;
use App\Models\OfferNegotiation;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\PropertyReview;
use App\Models\PropertyViewing;
use App\Models\RentSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->agentUser = User::create([
        'name' => 'Agent Selam',
        'email' => 'agent.selam@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Selam Properties',
        'email' => 'selam@example.com',
        'phone' => '+251 91 222 3344',
        'is_active' => true,
    ]);

    $this->property = Property::create([
        'property_code' => 'ETH-AA-VIL-00000001',
        'title' => 'Bole Luxury Villa',
        'slug' => 'bole-luxury-villa',
        'price' => 12000000.00,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 350,
        'building_area' => 300,
        'type' => 'sale',
        'property_category' => 'villa',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole, Addis Ababa',
        'agent_id' => $this->agent->id,
    ]);
});

test('guests are redirected to login when accessing client portal', function () {
    $response = $this->get(route('client.portal'));

    $response->assertRedirect(route('login'));
});

test('authenticated client can view their self-service portal', function () {
    $user = User::create([
        'name' => 'Abebe Kebede',
        'email' => 'abebe@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'buyer',
    ]);

    $response = $this->actingAs($user)->get(route('client.portal'));

    $response->assertStatus(200);
    $response->assertSee('Welcome back, Abebe Kebede.');
    $response->assertSee('My Offers');
    $response->assertSee('My Leases');
    $response->assertSee('Receipts');
});

test('client portal displays user offers, counteroffers, and agreements', function () {
    $client = User::create([
        'name' => 'Diaspora Investor',
        'email' => 'diaspora@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'buyer',
    ]);

    $offer = PropertyOffer::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'user_id' => $client->id,
        'buyer_name' => $client->name,
        'buyer_email' => $client->email,
        'buyer_phone' => '+251911223344',
        'offer_type' => 'purchase',
        'original_price' => 12000000,
        'offered_price' => 10500000,
        'currency' => 'ETB',
        'financing_type' => 'diaspora_remittance',
        'valid_until' => now()->addDays(7),
        'status' => 'countered',
        'current_counter_amount' => 11000000,
        'latest_counter_by' => 'seller_agent',
    ]);

    OfferNegotiation::create([
        'property_offer_id' => $offer->id,
        'sender_type' => 'seller_agent',
        'amount' => 11000000,
        'currency' => 'ETB',
        'message' => 'We can close at 11M ETB with quick transfer.',
    ]);

    $response = $this->actingAs($client)->get(route('client.portal'));

    $response->assertStatus(200);
    $response->assertSee('Bole Luxury Villa');
    $response->assertSee('10,500,000.00 ETB');
    $response->assertSee('11,000,000.00 ETB');
    $response->assertSee('Accept Proposed Price');
});

test('client can accept a counteroffer directly from portal', function () {
    $client = User::create([
        'name' => 'Buyer Dawit',
        'email' => 'buyer.dawit@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'buyer',
    ]);

    $offer = PropertyOffer::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'user_id' => $client->id,
        'buyer_name' => $client->name,
        'buyer_email' => $client->email,
        'buyer_phone' => '+251911000000',
        'offer_type' => 'purchase',
        'original_price' => 12000000,
        'offered_price' => 10500000,
        'currency' => 'ETB',
        'financing_type' => 'cash',
        'valid_until' => now()->addDays(7),
        'status' => 'countered',
        'current_counter_amount' => 11000000,
        'latest_counter_by' => 'seller_agent',
    ]);

    $response = $this->actingAs($client)->put(route('offers.accept', $offer->id));

    $response->assertRedirect();
    $offer->refresh();
    expect($offer->status)->toBe('accepted');
});

test('client can counter back an active offer', function () {
    $client = User::create([
        'name' => 'Buyer Dawit',
        'email' => 'buyer.dawit@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'buyer',
    ]);

    $offer = PropertyOffer::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'user_id' => $client->id,
        'buyer_name' => $client->name,
        'buyer_email' => $client->email,
        'buyer_phone' => '+251911000000',
        'offer_type' => 'purchase',
        'original_price' => 12000000,
        'offered_price' => 10500000,
        'currency' => 'ETB',
        'financing_type' => 'cash',
        'valid_until' => now()->addDays(7),
        'status' => 'countered',
        'current_counter_amount' => 11000000,
    ]);

    $response = $this->actingAs($client)->post(route('offers.counter', $offer->id), [
        'counter_amount' => 10800000,
        'message' => 'Meet in the middle at 10.8M ETB.',
    ]);

    $response->assertRedirect();
    $offer->refresh();
    expect((float) $offer->current_counter_amount)->toBe(10800000.0);
    expect($offer->status)->toBe('countered');
    expect($offer->latest_counter_by)->toBe('buyer');
});

test('tenant can view active lease and pay rent installment via payment engine', function () {
    $tenant = User::create([
        'name' => 'Tigist Alemu',
        'email' => 'tenant.tigist@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'buyer',
    ]);

    $rentalProperty = Property::create([
        'property_code' => 'ETH-AA-KIR-00000099',
        'title' => 'Kazanchis Apartment 3B',
        'slug' => 'kazanchis-apartment-3b',
        'price' => 45000.00,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 150,
        'building_area' => 150,
        'type' => 'rent',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Kazanchis, Addis Ababa',
        'agent_id' => $this->agent->id,
    ]);

    $lease = Lease::create([
        'property_id' => $rentalProperty->id,
        'landlord_user_id' => $this->agentUser->id,
        'tenant_user_id' => $tenant->id,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'tenant_phone' => '+251922334455',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addMonths(12)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 45000,
        'security_deposit' => 90000,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);

    $lease->generateSchedules();
    $firstSchedule = $lease->schedules()->first();

    // Tenant views portal
    $response = $this->actingAs($tenant)->get(route('client.portal'));
    $response->assertStatus(200);
    $response->assertSee('Kazanchis Apartment 3B');
    $response->assertSee('45,000.00 ETB');
    $response->assertSee($firstSchedule->period_label);

    // Tenant pays first installment via Telebirr (instant completion)
    $paymentResponse = $this->actingAs($tenant)->post(route('payments.store'), [
        'payment_type' => 'rent_installment',
        'payment_method' => 'telebirr',
        'amount' => 45000,
        'payer_name' => $tenant->name,
        'payer_email' => $tenant->email,
        'payer_phone' => '+251922334455',
        'payable_type' => RentSchedule::class,
        'payable_id' => $firstSchedule->id,
        'property_id' => $rentalProperty->id,
    ]);

    $firstSchedule->refresh();
    expect($firstSchedule->status)->toBe('paid');
    expect((float) $firstSchedule->amount_paid)->toBe(45000.0);

    // Verify payment record created
    $payment = Payment::where('payable_id', $firstSchedule->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->status)->toBe('completed');
    $paymentResponse->assertRedirect(route('payments.receipt', $payment->transaction_number));
});

test('client can cancel their viewing appointment', function () {
    $client = User::create([
        'name' => 'Viewing Client',
        'email' => 'viewing.client@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'buyer',
    ]);

    $viewing = PropertyViewing::create([
        'property_id' => $this->property->id,
        'user_id' => $client->id,
        'buyer_name' => $client->name,
        'buyer_email' => $client->email,
        'buyer_phone' => '+251911111111',
        'viewing_date' => now()->addDays(2)->format('Y-m-d'),
        'viewing_time' => '10:00:00',
        'viewing_type' => 'in_person',
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($client)->put(route('viewings.update-status', $viewing->id), [
        'status' => 'cancelled',
    ]);

    $response->assertRedirect();
    $viewing->refresh();
    expect($viewing->status)->toBe('cancelled');
});

test('buyer login redirects to buyer dashboard by default', function () {
    $buyer = User::create([
        'name' => 'New Buyer',
        'email' => 'newbuyer@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'buyer',
    ]);

    $response = $this->post(route('login'), [
        'email' => 'newbuyer@example.com',
        'password' => 'secret123',
    ]);

    $response->assertRedirect(route('buyer.dashboard'));
});

test('tenant can download their official lease agreement PDF from portal', function () {
    $tenant = User::create([
        'name' => 'Marta Tadesse',
        'email' => 'marta@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'buyer',
    ]);

    $lease = Lease::create([
        'property_id' => $this->property->id,
        'landlord_user_id' => $this->agentUser->id,
        'tenant_user_id' => $tenant->id,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'tenant_phone' => '+251911998877',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addMonths(12)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 50000,
        'security_deposit' => 100000,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);
    $lease->generateSchedules();

    $response = $this->actingAs($tenant)->get(route('leases.agreement', $lease->id));
    $response->assertOk();
});

test('guest can view housekeeping requests, reviews, and properties on portal', function () {
    $guest = User::create([
        'name' => 'Sara Alemayehu',
        'email' => 'sara@example.com',
        'password' => Hash::make('password123'),
        'role' => 'user',
    ]);

    $stayProperty = Property::create([
        'property_code' => 'ETH-AA-STY-00000002',
        'title' => 'Bole Medhanealem Luxury Suite',
        'slug' => 'bole-medhanealem-luxury-suite',
        'price' => 4500.00,
        'bedrooms' => 1,
        'bathrooms' => 1,
        'area' => 75,
        'building_area' => 75,
        'type' => 'rent',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanealem, Addis Ababa',
        'agent_id' => null, // GTM Admin Corporate Direct
    ]);

    $booking = Booking::create([
        'user_id' => $guest->id,
        'property_id' => $stayProperty->id,
        'room_number' => 'Suite 302',
        'guest_name' => $guest->name,
        'guest_email' => $guest->email,
        'guest_phone' => '+251911223344',
        'check_in_date' => now()->format('Y-m-d'),
        'check_out_date' => now()->addDays(3)->format('Y-m-d'),
        'total_nights' => 3,
        'guests_count' => 2,
        'nightly_price' => 4500.00,
        'accommodation_total' => 13500.00,
        'total_amount' => 13500.00,
        'currency' => 'ETB',
        'payment_status' => 'paid',
        'status' => 'checked_in',
        'checked_in_at' => now(),
    ]);

    // Guest service request
    GuestServiceRequest::create([
        'booking_id' => $booking->id,
        'property_id' => $stayProperty->id,
        'room_number' => 'Suite 302',
        'guest_name' => $guest->name,
        'service_type' => 'room_cleaning',
        'priority' => 'urgent',
        'description' => 'Fresh linens and afternoon tidy please.',
        'status' => 'pending',
    ]);

    // Review
    PropertyReview::create([
        'property_id' => $stayProperty->id,
        'user_id' => $guest->id,
        'booking_id' => $booking->id,
        'rating' => 5,
        'cleanliness_rating' => 5,
        'accuracy_rating' => 5,
        'communication_rating' => 5,
        'location_rating' => 5,
        'value_rating' => 5,
        'review_title' => 'Unbeatable hospitality',
        'review_body' => 'Superb room and instant front desk response!',
        'is_verified_stay' => true,
        'status' => 'published',
    ]);

    $response = $this->actingAs($guest)->get(route('client.portal'));

    $response->assertOk();
    $response->assertSee('Bole Medhanealem Luxury Suite');
    $response->assertSee('Suite 302');
    $response->assertSee('Room Cleaning / Housekeeping');
    $response->assertSee('Fresh linens and afternoon tidy please.');
    $response->assertSee('Unbeatable hospitality');
    $response->assertSee('Direct GTM HQ');
});

test('client portal renders vertical sidebar navigation modules, notification icon and profile avatar button', function () {
    $client = User::create([
        'name' => 'Kirubel Mekonnen',
        'email' => 'kirubel@example.com',
        'password' => Hash::make('password123'),
        'role' => 'user',
    ]);

    $response = $this->actingAs($client)->get(route('client.portal'));

    $response->assertOk();

    // Verify all 11 vertical navigation modules exist
    $response->assertSee('Overview');
    $response->assertSee('My Stays &amp; Desk', false);
    $response->assertSee('Maintenance &amp; Housekeeping', false);
    $response->assertSee('Guest Reviews');
    $response->assertSee('Properties (Admin vs Agent)');
    $response->assertSee('My Offers');
    $response->assertSee('Leases &amp; Rent', false);
    $response->assertSee('Viewings');
    $response->assertSee('Off-Plan Units');
    $response->assertSee('Receipts');
    $response->assertSee('Collections');

    // Verify header controls: notification icon and profile edit link
    $response->assertSee('id="portal-notifications-btn"', false);
    $response->assertSee('data-target-section="profile"', false);
    $response->assertSee('data-target-section="overview"', false);
    $response->assertSee('portal-hero-bg-img');
});
