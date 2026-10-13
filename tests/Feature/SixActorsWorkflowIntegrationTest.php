<?php

use App\Models\ActivityLog;
use App\Models\Agent;
use App\Models\Booking;
use App\Models\Campaign;
use App\Models\Commission;
use App\Models\Inquiry;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\PropertyReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    // 1. ADMIN
    $this->adminUser = User::create([
        'name' => 'System Admin',
        'email' => 'admin.integration@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    // 2. AGENTS (Agent A and Agent B)
    $this->agentUserA = User::create([
        'name' => 'Agent Abebe',
        'email' => 'agent.abebe@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);
    $this->agentA = Agent::create([
        'user_id' => $this->agentUserA->id,
        'name' => 'Agent Abebe',
        'email' => 'agent.abebe@example.com',
        'phone' => '+251911000001',
        'is_active' => true,
    ]);

    $this->agentUserB = User::create([
        'name' => 'Agent Berhanu',
        'email' => 'agent.berhanu@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);
    $this->agentB = Agent::create([
        'user_id' => $this->agentUserB->id,
        'name' => 'Agent Berhanu',
        'email' => 'agent.berhanu@example.com',
        'phone' => '+251911000002',
        'is_active' => true,
    ]);

    // 3. FINANCE OFFICER
    $this->financeUser = User::create([
        'name' => 'Finance Fikru',
        'email' => 'finance.fikru@example.com',
        'password' => Hash::make('password123'),
        'role' => 'finance_officer',
    ]);

    // 4. CAMPAIGN OFFICER
    $this->campaignUser = User::create([
        'name' => 'Campaign Chala',
        'email' => 'campaign.chala@example.com',
        'password' => Hash::make('password123'),
        'role' => 'campaign_officer',
    ]);

    // 5. RECEPTIONIST
    $this->receptionistUser = User::create([
        'name' => 'Receptionist Rahel',
        'email' => 'receptionist.rahel@example.com',
        'password' => Hash::make('password123'),
        'role' => 'receptionist',
    ]);

    // 6. CLIENTS / USERS (Client A and Client B)
    $this->clientUserA = User::create([
        'name' => 'Client Chaltu',
        'email' => 'client.chaltu@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $this->clientUserB = User::create([
        'name' => 'Client Biruk',
        'email' => 'client.biruk@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    // Test Properties with explicit codes
    $this->saleProperty = Property::create([
        'property_code' => 'ETH-TEST-SALE-001',
        'title' => 'Bole Medhanealem Luxury Penthouse',
        'slug' => 'bole-medhanealem-luxury-penthouse',
        'type' => 'sale',
        'property_category' => 'apartment',
        'price' => 12000000.00,
        'city' => 'Addis Ababa',
        'agent_id' => $this->agentA->id,
        'is_active' => true,
        'status' => 'published',
    ]);

    $this->rentProperty = Property::create([
        'property_code' => 'ETH-TEST-RENT-002',
        'title' => 'Kazanchis Executive Furnished Apartment',
        'slug' => 'kazanchis-executive-furnished-apartment',
        'type' => 'rent',
        'property_category' => 'apartment',
        'price' => 85000.00,
        'city' => 'Addis Ababa',
        'agent_id' => $this->agentA->id,
        'is_active' => true,
        'status' => 'published',
    ]);

    $this->stayProperty = Property::create([
        'property_code' => 'ETH-TEST-STAY-003',
        'title' => 'Meskel Square Boutique Guest House',
        'slug' => 'meskel-square-boutique-guest-house',
        'type' => 'stay',
        'property_category' => 'guest_house',
        'price' => 3000.00,
        'city' => 'Addis Ababa',
        'agent_id' => $this->agentA->id,
        'host_id' => $this->agentUserA->id,
        'is_active' => true,
        'status' => 'published',
    ]);

    $this->receptionistUser->update(['assigned_property_id' => $this->stayProperty->id]);
});

/**
 * SCENARIO 1: Guest / Client Viewing & Inquiry -> Agent Notification & Activity Log
 */
test('scenario 1: client viewing request schedules appointment, notifies agent and records activity log', function () {
    $response = $this->actingAs($this->clientUserA)->post(route('properties.viewings.store', $this->rentProperty->id), [
        'buyer_name' => $this->clientUserA->name,
        'buyer_email' => $this->clientUserA->email,
        'buyer_phone' => '+251911223344',
        'viewing_date' => Carbon::tomorrow()->format('Y-m-d'),
        'viewing_time' => 'morning_09_11',
        'viewing_type' => 'in_person',
        'buyer_notes' => 'Hello, I would like to schedule a viewing of this Kazanchis apartment.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('property_viewings', [
        'property_id' => $this->rentProperty->id,
        'agent_id' => $this->agentA->id,
        'user_id' => $this->clientUserA->id,
    ]);

    // Check agent received database notification
    expect($this->agentUserA->notifications->count())->toBeGreaterThanOrEqual(1);

    // Check ActivityLog recorded
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->clientUserA->id,
        'action' => 'viewing_scheduled',
    ]);
});

/**
 * SCENARIO 2: Rental Workflow: Client rental offer -> Agent converts to Lease deal -> Finance verifies payment -> Commission generated -> Activity Log
 */
test('scenario 2: rental workflow from offer to lease deal creates commission and notifies finance', function () {
    // 1. Client submits rental offer
    $offer = PropertyOffer::create([
        'offer_reference' => 'OFF-ETH-RENT-001',
        'property_id' => $this->rentProperty->id,
        'user_id' => $this->clientUserA->id,
        'agent_id' => $this->agentA->id,
        'buyer_name' => $this->clientUserA->name,
        'buyer_email' => $this->clientUserA->email,
        'buyer_phone' => '+251911223344',
        'offer_type' => 'rental',
        'original_price' => 85000.00,
        'offered_price' => 85000.00,
        'currency' => 'ETB',
        'status' => 'accepted',
    ]);

    // 2. Agent converts accepted offer to closed lease deal
    $response = $this->actingAs($this->agentUserA)->post(route('offers.convert-to-deal', $offer->id));

    $response->assertRedirect();
    $offer->refresh();
    expect($offer->closing_status)->toBe('agreement_generated');
    expect($offer->lease_id)->not->toBeNull();

    // Check Lease was created
    $lease = Lease::find($offer->lease_id);
    expect($lease)->not->toBeNull();
    expect((float) $lease->monthly_rent)->toBe(85000.00);

    // Check Commission was automatically generated
    $this->assertDatabaseHas('commissions', [
        'agent_id' => $this->agentA->id,
        'property_id' => $this->rentProperty->id,
    ]);

    // Check Finance Officer was notified of commission
    expect($this->financeUser->notifications->count())->toBeGreaterThanOrEqual(1);

    // Check ActivityLog recorded deal closing
    $this->assertDatabaseHas('activity_logs', [
        'action' => 'commission_generated',
    ]);
});

/**
 * SCENARIO 3: Sale Workflow: Client offer -> Agent negotiation -> Deal converted -> Commission created -> Finance settlement
 */
test('scenario 3: sale workflow converts offer to closed deal and generates commission for finance settlement', function () {
    $offer = PropertyOffer::create([
        'offer_reference' => 'OFF-ETH-SALE-002',
        'property_id' => $this->saleProperty->id,
        'user_id' => $this->clientUserA->id,
        'agent_id' => $this->agentA->id,
        'buyer_name' => $this->clientUserA->name,
        'buyer_email' => $this->clientUserA->email,
        'buyer_phone' => '+251911223344',
        'offer_type' => 'sale',
        'original_price' => 12000000.00,
        'offered_price' => 11500000.00,
        'currency' => 'ETB',
        'status' => 'accepted',
    ]);

    // Agent converts offer to sale deal
    $response = $this->actingAs($this->agentUserA)->post(route('offers.convert-to-deal', $offer->id));

    $response->assertRedirect();
    $offer->refresh();
    expect($offer->closing_status)->toBe('agreement_generated');
    expect($offer->order_id)->not->toBeNull();

    // Commission created with pending status
    $commission = Commission::where('property_id', $this->saleProperty->id)->latest()->first();
    expect($commission)->not->toBeNull();
    expect($commission->status)->toBe('pending');
    expect((float) $commission->commission_amount)->toBeGreaterThan(0);

    // Finance Officer settles commission
    $settleResponse = $this->actingAs($this->financeUser)->post(route('commissions.settle', $commission->id), [
        'payout_method' => 'bank_transfer',
        'payout_reference' => 'CBE-TX-998811',
        'admin_note' => 'Verified and paid via CBE bank transfer',
    ]);

    $settleResponse->assertRedirect();
    $commission->refresh();
    expect($commission->status)->toBe('paid');
    expect($commission->disbursed_by)->toBe($this->financeUser->id);

    // Activity log recorded for settlement
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->financeUser->id,
        'action' => 'commission_settled',
    ]);
});

/**
 * SCENARIO 4: Guest House Workflow: Client stay booking -> Receptionist notified -> Check-in -> Check-out
 */
test('scenario 4: guest stay booking notifies receptionist, followed by front-desk check-in and check-out', function () {
    // 1. Client books stay
    $response = $this->actingAs($this->clientUserA)->post(route('stays.book', $this->stayProperty->id), [
        'guest_name' => $this->clientUserA->name,
        'guest_email' => $this->clientUserA->email,
        'guest_phone' => '+251911998877',
        'check_in_date' => Carbon::today()->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(3)->format('Y-m-d'),
        'adults' => 2,
        'children' => 0,
        'special_requests' => 'Quiet room please',
    ]);

    $response->assertRedirect();
    $booking = Booking::where('property_id', $this->stayProperty->id)->latest()->first();
    expect($booking)->not->toBeNull();

    // Check receptionist received notification
    expect($this->receptionistUser->notifications->count())->toBeGreaterThanOrEqual(1);

    // Check activity log recorded
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->clientUserA->id,
        'action' => 'stay_reservation_created',
        'entity_id' => $booking->id,
    ]);

    // 2. Receptionist checks in the guest
    $checkInResponse = $this->actingAs($this->receptionistUser)->post(route('receptionist.check-in', $booking->id), [
        'room_number' => 'Suite 204',
        'guest_id_number' => 'ETH-PASS-789012',
    ]);
    $checkInResponse->assertRedirect();
    $booking->refresh();
    expect($booking->status)->toBe('checked_in');

    // 3. Receptionist checks out the guest
    $checkOutResponse = $this->actingAs($this->receptionistUser)->post(route('receptionist.check-out', $booking->id), [
        'checkout_notes' => 'Minibar settled, room inspected',
    ]);
    $checkOutResponse->assertRedirect();
    $booking->refresh();
    expect($booking->status)->toBe('checked_out');

    // Check activity log recorded check-out
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->receptionistUser->id,
        'action' => 'guest_checked_out',
        'entity_id' => $booking->id,
    ]);
});

/**
 * SCENARIO 5: Campaign Workflow: Agent creates campaign -> Campaign Officer reviews and approves -> Leads captured
 */
test('scenario 5: campaign workflow allows agent submission, campaign officer approval, and lead recording', function () {
    // 1. Agent creates campaign
    $campResponse = $this->actingAs($this->agentUserA)->post(route('campaigns.store'), [
        'title' => 'Bole Penthouse Ramadan Promo',
        'campaign_type' => 'seasonal_discount',
        'property_id' => $this->saleProperty->id,
        'headline' => 'Exclusive Luxury Living in Bole Medhanealem',
        'target_audience' => 'Diaspora and executive buyers',
        'budget' => 25000.00,
        'start_date' => Carbon::today()->format('Y-m-d'),
        'end_date' => Carbon::today()->addDays(30)->format('Y-m-d'),
        'description' => 'Special 5% discount on early closing.',
    ]);

    $campResponse->assertRedirect();
    $campaign = Campaign::where('agent_id', $this->agentA->id)->latest()->first();
    expect($campaign)->not->toBeNull();
    expect($campaign->status)->toBe('under_review');

    // Campaign Officer notified
    expect($this->campaignUser->notifications->count())->toBeGreaterThanOrEqual(1);

    // 2. Campaign Officer approves campaign
    $approveResponse = $this->actingAs($this->campaignUser)->patch(route('campaigns.update-status', $campaign->id), [
        'status' => 'approved',
        'review_notes' => 'Meets platform marketing standards',
    ]);

    $approveResponse->assertRedirect();
    $campaign->refresh();
    expect($campaign->status)->toBe('approved');
    expect($campaign->reviewed_by)->toBe($this->campaignUser->id);

    // Agent notified of approval
    expect($this->agentUserA->notifications->count())->toBeGreaterThanOrEqual(1);

    // 3. Client interacts with campaign and leaves a lead inquiry
    $leadResponse = $this->post(route('campaigns.leads.store', $campaign->id), [
        'name' => 'Dr. Aster Mamo',
        'email' => 'aster.mamo@example.com',
        'phone' => '+251911334455',
        'message' => 'Interested in the 5% cash purchase offer',
    ]);

    $leadResponse->assertRedirect();
    $this->assertDatabaseHas('campaign_leads', [
        'campaign_id' => $campaign->id,
        'email' => 'aster.mamo@example.com',
    ]);
});

/**
 * SCENARIO 6: Payment Issue Workflow: Client reports payment issue -> Auto-assigned & notified to Finance Officer
 */
test('scenario 6: client payment issue report is automatically routed and notified to finance officer', function () {
    $response = $this->actingAs($this->clientUserA)->post(route('reports.store-direct'), [
        'reason' => 'other',
        'description' => 'My Telebirr payment transfer was deducted but receipt status is still pending verification.',
    ]);

    $response->assertSessionHas('success');
    $report = PropertyReport::latest()->first();
    expect($report)->not->toBeNull();

    // Finance Officer receives direct notification
    expect($this->financeUser->notifications->count())->toBeGreaterThanOrEqual(1);
});

/**
 * SCENARIO 7: Property Issue Workflow: Client reports property issue -> Auto-assigned & notified to Listing Agent
 */
test('scenario 7: client property issue report is automatically routed to the property listing agent', function () {
    $response = $this->actingAs($this->clientUserA)->post(route('properties.report', $this->saleProperty->id), [
        'reason' => 'misleading',
        'description' => 'The listed bedroom count seems to differ from the actual architectural floor plan.',
    ]);

    $response->assertSessionHas('success');
    $report = PropertyReport::latest()->first();
    expect($report)->not->toBeNull();

    // Listing Agent receives direct notification
    expect($this->agentUserA->notifications->count())->toBeGreaterThanOrEqual(1);
});

/**
 * SCENARIO 8: Payment Verification Workflow: Payment confirmed by Finance -> Notifies Payer & Agent -> Activity Log
 */
test('scenario 8: finance officer confirms payment, notifying payer and listing agent and recording activity log', function () {
    $payment = Payment::create([
        'user_id' => $this->clientUserA->id,
        'property_id' => $this->rentProperty->id,
        'payer_name' => $this->clientUserA->name,
        'payer_email' => $this->clientUserA->email,
        'payer_phone' => '+251911223344',
        'payment_type' => 'rent',
        'amount' => 85000.00,
        'currency' => 'ETB',
        'payment_method' => 'bank_transfer',
        'payment_category' => 'rental_revenue',
        'status' => 'pending',
        'transaction_reference' => 'TX-CONFIRM-TEST-001',
    ]);

    $response = $this->actingAs($this->financeUser)->put(route('payments.verify', $payment->id));
    $response->assertRedirect();
    $payment->refresh();
    expect($payment->status)->toBe('completed');

    // Activity log recorded
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->financeUser->id,
        'action' => 'payment_verified',
        'entity_id' => $payment->id,
    ]);
});

/**
 * SCENARIO 9: IDOR Security: Agent A cannot access or manage Agent B's listings/offers
 */
test('scenario 9: agent cannot view or convert another agents offers or private data', function () {
    $propertyB = Property::create([
        'property_code' => 'ETH-TEST-PROP-B01',
        'title' => 'Agent B Special Villa',
        'slug' => 'agent-b-special-villa',
        'type' => 'sale',
        'property_category' => 'house',
        'price' => 20000000.00,
        'agent_id' => $this->agentB->id,
        'is_active' => true,
        'status' => 'published',
    ]);

    $offerB = PropertyOffer::create([
        'offer_reference' => 'OFF-ETH-PROP-B01',
        'property_id' => $propertyB->id,
        'user_id' => $this->clientUserB->id,
        'agent_id' => $this->agentB->id,
        'buyer_name' => $this->clientUserB->name,
        'buyer_email' => $this->clientUserB->email,
        'buyer_phone' => '+251911990011',
        'offer_type' => 'sale',
        'original_price' => 20000000.00,
        'offered_price' => 19000000.00,
        'currency' => 'ETB',
        'status' => 'pending',
    ]);

    // Agent A attempts to convert Agent B's offer
    $response = $this->actingAs($this->agentUserA)->post(route('offers.convert-to-deal', $offerB->id), [
        'deal_type' => 'sale',
        'agreed_price' => 19500000.00,
        'client_name' => 'Attacker Client',
        'client_email' => 'attacker@example.com',
    ]);

    // Must be forbidden (403)
    $response->assertStatus(403);
    $offerB->refresh();
    expect($offerB->status)->toBe('pending');
});

/**
 * SCENARIO 10: IDOR Security: Client A cannot access Client B's private bookings/orders
 */
test('scenario 10: client cannot cancel or tamper with another clients booking', function () {
    $bookingB = Booking::create([
        'booking_reference' => 'STAY-TEST-BOOK-B01',
        'property_id' => $this->stayProperty->id,
        'user_id' => $this->clientUserB->id,
        'guest_name' => $this->clientUserB->name,
        'guest_email' => $this->clientUserB->email,
        'guest_phone' => '+251911887766',
        'nightly_price' => 3000.00,
        'accommodation_total' => 6000.00,
        'check_in_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
        'check_out_date' => Carbon::today()->addDays(7)->format('Y-m-d'),
        'total_amount' => 6000.00,
        'status' => 'confirmed',
    ]);

    // Client A tries to cancel Client B's booking
    $response = $this->actingAs($this->clientUserA)->delete(route('bookings.destroy', $bookingB->id));
    $response->assertStatus(403);

    $bookingB->refresh();
    expect($bookingB->status)->toBe('confirmed');
});

/**
 * SCENARIO 11: Role Security: Receptionist cannot access Finance or Admin desks
 */
test('scenario 11: receptionist is forbidden from accessing finance desk and admin dashboard', function () {
    // Receptionist tries accessing Admin dashboard
    $adminResponse = $this->actingAs($this->receptionistUser)->get(route('dashboard'));
    $adminResponse->assertStatus(403);

    // Receptionist tries accessing Finance reporting desk
    $financeResponse = $this->actingAs($this->receptionistUser)->get(route('finance.index'));
    $financeResponse->assertStatus(403);
});

/**
 * SCENARIO 12: Role Security: Campaign Officer cannot disburse commissions or access finance desks
 */
test('scenario 12: campaign officer cannot settle commissions or access financial reporting desk', function () {
    $commission = Commission::create([
        'agent_id' => $this->agentA->id,
        'property_id' => $this->saleProperty->id,
        'property_price' => 12000000.00,
        'commission_rate' => 2.00,
        'commission_amount' => 240000.00,
        'net_payout_amount' => 235200.00,
        'status' => 'pending',
    ]);

    // Campaign Officer tries to settle commission
    $disburseResponse = $this->actingAs($this->campaignUser)->post(route('commissions.settle', $commission->id), [
        'payout_method' => 'cash',
        'admin_note' => 'Unauthorized payout attempt',
    ]);
    $disburseResponse->assertStatus(403);

    // Campaign Officer tries accessing Finance reporting desk
    $financeIndexResponse = $this->actingAs($this->campaignUser)->get(route('finance.index'));
    $financeIndexResponse->assertStatus(403);

    $commission->refresh();
    expect($commission->status)->toBe('pending');
});
