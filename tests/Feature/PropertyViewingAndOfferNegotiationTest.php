<?php

use App\Models\Agent;
use App\Models\OfferNegotiation;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\PropertyViewing;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->adminUser = User::create([
        'name' => 'Admin Manager',
        'email' => 'admin.viewing@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->agentUser = User::create([
        'name' => 'Listing Agent Almaz',
        'email' => 'agent.almaz@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Almaz Haile',
        'email' => 'almaz@example.com',
        'phone' => '+251 91 123 7788',
        'is_active' => true,
    ]);

    $this->buyerUser = User::create([
        'name' => 'Buyer Solomon',
        'email' => 'buyer.solomon@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->property = Property::create([
        'property_code' => 'ETH-AA-BOLE-00000088',
        'title' => 'Bole Brass Contemporary Luxury Villa',
        'slug' => 'bole-brass-contemporary-luxury-villa',
        'price' => 38000000.00,
        'bedrooms' => 5,
        'bathrooms' => 5,
        'area' => 450,
        'building_area' => 520,
        'floors' => 2,
        'parking_spaces' => 4,
        'condition' => 'excellent',
        'has_electricity' => true,
        'has_water' => true,
        'has_generator' => true,
        'has_water_tank' => true,
        'has_security' => true,
        'type' => 'sale',
        'property_category' => 'villa',
        'status' => 'published',
        'city' => 'Addis Ababa',
        'address' => 'Bole Brass Corridor, Addis Ababa',
        'agent_id' => $this->agent->id,
        'is_active' => true,
    ]);
});

it('allows a buyer to schedule a viewing appointment with valid data', function () {
    $viewingDate = date('Y-m-d', strtotime('+2 days'));

    $response = $this->actingAs($this->buyerUser)
        ->from(route('properties.show', $this->property->slug))
        ->post(route('properties.viewings.store', $this->property), [
            'buyer_name' => 'Solomon Kassa',
            'buyer_email' => 'solomon@example.com',
            'buyer_phone' => '+251 91 199 8877',
            'viewing_date' => $viewingDate,
            'viewing_time' => 'morning_09_11',
            'viewing_type' => 'in_person',
            'buyer_notes' => 'Please confirm if the compound allows parking for 4 SUVs during inspection.',
        ]);

    $response->assertRedirect(route('properties.show', $this->property->slug));
    $response->assertSessionHas('viewing_scheduled_success');

    $this->assertDatabaseHas('property_viewings', [
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'user_id' => $this->buyerUser->id,
        'buyer_name' => 'Solomon Kassa',
        'viewing_time' => 'morning_09_11',
        'viewing_type' => 'in_person',
        'status' => 'requested',
    ]);

    $viewing = PropertyViewing::where('property_id', $this->property->id)->first();
    expect($viewing->viewing_reference)->toStartWith('VIEW-AA-');
    expect($viewing->formatted_time_slot)->toBe('Morning (9:00 AM - 11:30 AM)');
});

it('validates viewing appointment fields properly', function () {
    $response = $this->post(route('properties.viewings.store', $this->property), [
        'buyer_name' => '',
        'buyer_email' => 'not-an-email',
        'buyer_phone' => '',
        'viewing_date' => date('Y-m-d', strtotime('-1 day')),
        'viewing_time' => 'midnight_slot',
        'viewing_type' => 'space_teleportation',
    ]);

    $response->assertSessionHasErrors([
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'viewing_date',
        'viewing_time',
        'viewing_type',
    ]);
});

it('allows listing agent to update viewing appointment status', function () {
    $viewing = PropertyViewing::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'user_id' => $this->buyerUser->id,
        'buyer_name' => 'Solomon Kassa',
        'buyer_email' => 'solomon@example.com',
        'buyer_phone' => '+251 91 199 8877',
        'viewing_date' => date('Y-m-d', strtotime('+3 days')),
        'viewing_time' => 'afternoon_14_16',
        'viewing_type' => 'virtual_tour',
        'status' => 'requested',
    ]);

    $response = $this->actingAs($this->agentUser)
        ->put(route('viewings.update-status', $viewing), [
            'status' => 'confirmed',
            'agent_notes' => 'Confirmed with client via WhatsApp call.',
        ]);

    $response->assertRedirect();
    expect($viewing->fresh()->status)->toBe('confirmed');
    expect($viewing->fresh()->agent_notes)->toBe('Confirmed with client via WhatsApp call.');
});

it('prevents unauthorized users from updating viewing appointment status', function () {
    $otherUser = User::create([
        'name' => 'Other Random User',
        'email' => 'other.random@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $viewing = PropertyViewing::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'buyer_name' => 'Client',
        'buyer_email' => 'client@example.com',
        'buyer_phone' => '+251 91 123 4567',
        'viewing_date' => date('Y-m-d', strtotime('+2 days')),
        'viewing_time' => 'morning_09_11',
        'viewing_type' => 'in_person',
        'status' => 'requested',
    ]);

    $response = $this->actingAs($otherUser)
        ->put(route('viewings.update-status', $viewing), [
            'status' => 'confirmed',
        ]);

    $response->assertStatus(403);
    expect($viewing->fresh()->status)->toBe('requested');
});

it('allows buyer to submit a formal purchase offer with full specifications', function () {
    $response = $this->actingAs($this->buyerUser)
        ->from(route('properties.show', $this->property->slug))
        ->post(route('properties.offers.store', $this->property), [
            'buyer_name' => 'Solomon Kassa',
            'buyer_email' => 'solomon@example.com',
            'buyer_phone' => '+251 91 199 8877',
            'offered_price' => 36500000.00,
            'financing_type' => 'diaspora_remittance',
            'earnest_deposit' => 3650000.00,
            'validity_days' => 14,
            'buyer_notes' => 'Settlement via Commercial Bank of Ethiopia (CBE) USD diaspora account with 10% earnest money deposited upon agreement.',
        ]);

    $response->assertRedirect(route('properties.show', $this->property->slug));
    $response->assertSessionHas('offer_submitted_success');

    $this->assertDatabaseHas('property_offers', [
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'user_id' => $this->buyerUser->id,
        'buyer_name' => 'Solomon Kassa',
        'offered_price' => 36500000.00,
        'financing_type' => 'diaspora_remittance',
        'deposit_proposed' => 3650000.00,
        'status' => 'submitted',
    ]);

    $offer = PropertyOffer::where('property_id', $this->property->id)->first();
    expect($offer->offer_reference)->toStartWith('OFF-ETH-');
    expect((float) $offer->original_price)->toBe(38000000.00);
    expect($offer->difference_percentage)->toBe(-3.9);

    // Initial proposal must be recorded in offer_negotiations ledger
    $this->assertDatabaseHas('offer_negotiations', [
        'property_offer_id' => $offer->id,
        'sender_type' => 'buyer',
        'amount' => 36500000.00,
    ]);
});

it('handles string form data when submitting a formal offer without carbon type errors', function () {
    $response = $this->actingAs($this->buyerUser)
        ->from(route('properties.show', $this->property->slug))
        ->post(route('properties.offers.store', $this->property), [
            'buyer_name' => 'Sara Alemayehu',
            'buyer_email' => 'sara@example.com',
            'buyer_phone' => '+251 92 333 4455',
            'offered_price' => '35000000',
            'financing_type' => 'cash',
            'earnest_deposit' => '3500000',
            'validity_days' => '7',
            'buyer_notes' => 'Full cash payment within 14 days.',
        ]);

    $response->assertRedirect(route('properties.show', $this->property->slug));
    $response->assertSessionHas('offer_submitted_success');

    $offer = PropertyOffer::where('buyer_email', 'sara@example.com')->first();
    expect($offer)->not->toBeNull();
    expect((float) $offer->offered_price)->toBe(35000000.00);
    expect($offer->valid_until)->not->toBeNull();
});

it('tracks multi-round counteroffers between agent and buyer', function () {
    $offer = PropertyOffer::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'user_id' => $this->buyerUser->id,
        'buyer_name' => 'Solomon Kassa',
        'buyer_email' => 'solomon@example.com',
        'buyer_phone' => '+251 91 199 8877',
        'original_price' => 38000000.00,
        'offered_price' => 35000000.00,
        'financing_type' => 'cash',
        'status' => 'pending',
    ]);

    OfferNegotiation::create([
        'property_offer_id' => $offer->id,
        'sender_type' => 'buyer',
        'amount' => 35000000.00,
        'message' => 'Initial buyer proposal for quick cash closing.',
    ]);

    // Round 1: Agent counters with 37,200,000 ETB
    $response1 = $this->actingAs($this->agentUser)
        ->post(route('offers.counter', $offer), [
            'counter_amount' => 37200000.00,
            'message' => 'Owner cannot accept 35M, but agrees to 37.2M including luxury fitted kitchen.',
        ]);

    $response1->assertRedirect();
    $offer->refresh();
    expect($offer->status)->toBe('countered');
    expect((float) $offer->current_counter_amount)->toBe(37200000.00);
    expect($offer->latest_counter_by)->toBe('seller_agent');

    // Round 2: Buyer counters back with 36,800,000 ETB
    $response2 = $this->actingAs($this->buyerUser)
        ->post(route('offers.counter', $offer), [
            'counter_amount' => 36800000.00,
            'message' => 'Final buyer budget limit is 36.8M ETB cash.',
        ]);

    $response2->assertRedirect();
    $offer->refresh();
    expect($offer->status)->toBe('countered');
    expect((float) $offer->current_counter_amount)->toBe(36800000.00);
    expect($offer->latest_counter_by)->toBe('buyer');

    expect($offer->negotiations()->count())->toBe(3);

    // Round 3: Agent accepts deal at 36,800,000 ETB
    $response3 = $this->actingAs($this->agentUser)
        ->put(route('offers.accept', $offer));

    $response3->assertRedirect();
    $offer->refresh();
    expect($offer->status)->toBe('accepted');

    // Verify final negotiation ledger event
    $latestEvent = $offer->negotiations()->reorder()->latest('id')->first();
    expect($latestEvent->message)->toContain('Offer officially accepted');
});

it('allows seller agent or admin to decline an offer', function () {
    $offer = PropertyOffer::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'user_id' => $this->buyerUser->id,
        'buyer_name' => 'Low Baller',
        'buyer_email' => 'low@example.com',
        'buyer_phone' => '+251 91 000 0000',
        'original_price' => 38000000.00,
        'offered_price' => 20000000.00,
        'financing_type' => 'installments',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->agentUser)
        ->put(route('offers.reject', $offer), [
            'reason' => 'Offered price is substantially below acceptable floor value.',
        ]);

    $response->assertRedirect();
    expect($offer->fresh()->status)->toBe('rejected');
    expect(OfferNegotiation::where('property_offer_id', $offer->id)->count())->toBe(1);
});

it('renders viewing and offer desks in admin dashboard and agent portal', function () {
    PropertyViewing::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'buyer_name' => 'Tigist Alemu',
        'buyer_email' => 'tigist@example.com',
        'buyer_phone' => '+251 91 333 4455',
        'viewing_date' => date('Y-m-d', strtotime('+1 day')),
        'viewing_time' => 'morning_09_11',
        'viewing_type' => 'in_person',
        'status' => 'confirmed',
    ]);

    PropertyOffer::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'user_id' => $this->buyerUser->id,
        'buyer_name' => 'Solomon Kassa',
        'buyer_email' => 'solomon@example.com',
        'buyer_phone' => '+251 91 199 8877',
        'original_price' => 38000000.00,
        'offered_price' => 36500000.00,
        'financing_type' => 'cash',
        'status' => 'pending',
    ]);

    // Admin dashboard
    $adminResponse = $this->actingAs($this->adminUser)->get(route('dashboard'));
    $adminResponse->assertOk();
    $adminResponse->assertSee('Property Viewings');
    $adminResponse->assertSee('Formal Offer & Negotiation Desk', false);
    $adminResponse->assertSee('Tigist Alemu');
    $adminResponse->assertSee('36,500,000');
    $adminResponse->assertSee('dashboard-messages-icon');

    // Agent portal
    $agentResponse = $this->actingAs($this->agentUser)->get(route('agent.portal'));
    $agentResponse->assertOk();
    $agentResponse->assertSee('Viewings');
    $agentResponse->assertSee('Offers & Negotiations');
    $agentResponse->assertSee('Tigist Alemu');
    $agentResponse->assertSee('36,500,000');
    $agentResponse->assertSee('agent-messages-icon');

    // Public property page
    $publicResponse = $this->get(route('properties.show', $this->property->slug));
    $publicResponse->assertOk();
    $publicResponse->assertSee('Schedule a Property Viewing');
    $publicResponse->assertSee('Make Formal Offer & Negotiation Desk', false);
});
