<?php

use App\Models\Agent;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->agentUser = User::create([
        'name' => 'CRM Agent',
        'email' => 'crm.agent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'CRM Brokerage',
        'email' => 'crm.brokerage@example.com',
        'phone' => '+251 91 123 4567',
        'bio' => 'Listing specialist',
    ]);

    $this->property = Property::create([
        'title' => 'CRM Listing Villa',
        'slug' => 'crm-listing-villa',
        'description' => 'A modern four bedroom villa with a private garden, secure parking and fiber internet in a quiet Bole neighborhood.',
        'price' => 18000000,
        'type' => 'sale',
        'status' => 'available',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole Subcity, Woreda 03',
        'agent_id' => $this->agent->id,
    ]);

    $this->lead = Inquiry::create([
        'name' => 'Lead Lidia',
        'email' => 'lidia.lead@example.com',
        'phone' => '+251 91 888 7777',
        'subject' => 'Is the villa negotiable?',
        'message' => 'I would like to schedule a viewing next week if the price allows.',
        'status' => 'new',
        'agent_id' => $this->agent->id,
    ]);

    $this->offer = PropertyOffer::create([
        'offer_reference' => 'OFF-CRM-0001',
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'buyer_name' => 'Offer Owner',
        'buyer_email' => 'offer.owner@example.com',
        'buyer_phone' => '+251 91 222 3333',
        'original_price' => 18000000,
        'offered_price' => 16500000,
        'currency' => 'ETB',
        'status' => 'pending',
    ]);

    $this->order = Order::create([
        'property_id' => $this->property->id,
        'agent_id' => $this->agent->id,
        'type' => 'buy',
        'name' => 'Buyer Berhanu',
        'email' => 'berhanu.buyer@example.com',
        'phone' => '+251 91 555 6666',
        'offer_amount' => 18000000,
        'status' => 'completed',
    ]);
});

test('agent portal renders the renewed CRM dashboard and every desk', function () {
    $response = $this->actingAs($this->agentUser)->get(route('agent.portal'));

    $response->assertOk();
    $response->assertSee('Agent CRM');
    $response->assertSee('Sales Command Center');
    $response->assertSee('Leads');
    $response->assertSee('New Leads');
    $response->assertSee('Closed Deals');
    $response->assertSee('Pipeline');
    $response->assertSee('Response rate');

    $response->assertSee('Leads & Pipeline');
    $response->assertSee('Viewings');
    $response->assertSee('Offers & Negotiations');
    $response->assertSee('Lease and Rental');
    $response->assertSee('Commissions');
    $response->assertSee('Clients');
    $response->assertSee('Listing quality');
});

test('agent portal counts CRM metrics from real records', function () {
    $response = $this->actingAs($this->agentUser)->get(route('agent.portal'));
    $response->assertOk();

    $crmStats = $response->viewData('crmStats');

    expect($crmStats['leads'])->toBe(1);
    expect($crmStats['new_leads'])->toBe(1);
    expect($crmStats['active_offers'])->toBe(1);
    expect($crmStats['closed_deals'])->toBe(1);
    expect($crmStats['clients'])->toBe(1);
});

test('clients desk lists the buyer who closed a deal on the agent listing', function () {
    $response = $this->actingAs($this->agentUser)->get(route('agent.portal'));

    $response->assertOk();
    $response->assertSee('Buyer Berhanu');
    $response->assertSee('berhanu.buyer@example.com');
    $response->assertSee('18,000,000');
});

test('agent can advance a lead through the pipeline', function () {
    $this->actingAs($this->agentUser)
        ->put(route('inquiries.update-status', $this->lead), ['status' => 'viewing'])
        ->assertRedirect();

    expect($this->lead->fresh()->status)->toBe('viewing');
    expect($this->lead->fresh()->read_at)->not()->toBeNull();
});

test('agent cannot advance a lead that belongs to another agent', function () {
    $otherAgentUser = User::create([
        'name' => 'Other Agent',
        'email' => 'crm.other.agent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    Agent::create([
        'user_id' => $otherAgentUser->id,
        'name' => 'Other Brokerage',
        'email' => 'crm.other.brokerage@example.com',
    ]);

    $this->actingAs($otherAgentUser)
        ->put(route('inquiries.update-status', $this->lead), ['status' => 'closed'])
        ->assertForbidden();

    expect($this->lead->fresh()->status)->toBe('new');
});

test('agent loses pipeline triage when leads permission is revoked', function () {
    UserPermissionOverride::create([
        'user_id' => $this->agentUser->id,
        'permission_id' => Permission::query()->where('key', 'leads.manage')->value('id'),
        'granted' => false,
    ]);

    $this->actingAs($this->agentUser->fresh())
        ->put(route('inquiries.update-status', $this->lead), ['status' => 'contacted'])
        ->assertForbidden();

    expect($this->lead->fresh()->status)->toBe('new');
});

test('agent can counter an offer from the negotiation desk', function () {
    $this->actingAs($this->agentUser)
        ->post(route('offers.counter', $this->offer), [
            'counter_amount' => 17200000,
            'message' => 'Countering at 17.2M to close the gap.',
        ])
        ->assertRedirect();

    $offer = $this->offer->fresh();

    expect((float) $offer->current_counter_amount)->toBe(17200000.0);
});

test('agent profile stores service locations languages and phone privacy', function () {
    $this->actingAs($this->agentUser)
        ->put(route('agent.profile.update'), [
            'name' => 'CRM Brokerage',
            'email' => 'crm.brokerage@example.com',
            'bio' => 'Listing specialist',
            'service_locations' => ['Bole', 'CMC'],
            'languages' => ['Amharic', 'English'],
            'phone_privacy' => 'messaging_only',
        ])
        ->assertRedirect(route('agent.portal'));

    $agent = $this->agent->fresh();

    expect($agent->service_locations)->toBe(['Bole', 'CMC']);
    expect($agent->languages)->toBe(['Amharic', 'English']);
    expect($agent->phone_privacy)->toBe('messaging_only');
});
