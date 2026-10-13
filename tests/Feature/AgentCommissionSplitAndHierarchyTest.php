<?php

use App\Models\Agent;
use App\Models\Commission;
use App\Models\Property;
use App\Models\User;
use App\Services\CommissionSplitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'System Admin',
        'email' => 'admin@realestate.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->listingAgentUser = User::create([
        'name' => 'Listing Agent Almaz',
        'email' => 'almaz@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->listingAgent = Agent::create([
        'user_id' => $this->listingAgentUser->id,
        'name' => 'Almaz Real Estate',
        'email' => 'almaz@example.com',
        'phone' => '+251 91 100 2000',
        'is_active' => true,
        'tier' => 'junior',
        'split_percentage' => 60.00,
        'license_number' => 'ETH-BRK-AA-2024-001',
        'license_verified' => true,
    ]);

    $this->sellingAgentUser = User::create([
        'name' => 'Selling Agent Dawit',
        'email' => 'dawit@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->sellingAgent = Agent::create([
        'user_id' => $this->sellingAgentUser->id,
        'name' => 'Dawit Realty Group',
        'email' => 'dawit@example.com',
        'phone' => '+251 92 200 3000',
        'is_active' => true,
        'tier' => 'senior',
        'split_percentage' => 70.00,
        'license_number' => 'ETH-BRK-AA-2024-002',
        'license_verified' => true,
    ]);

    $this->referralAgentUser = User::create([
        'name' => 'Referral Agent Bethlehem',
        'email' => 'bethlehem@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->referralAgent = Agent::create([
        'user_id' => $this->referralAgentUser->id,
        'name' => 'Bethlehem Consulting',
        'email' => 'bethlehem@example.com',
        'phone' => '+251 93 300 4000',
        'is_active' => true,
        'tier' => 'principal',
        'split_percentage' => 80.00,
        'license_number' => 'ETH-BRK-AA-2024-003',
        'license_verified' => true,
    ]);

    $this->property = Property::create([
        'property_code' => 'ETH-AA-APT-SPLIT001',
        'title' => 'Bole Medhanialem Luxury Penthouse',
        'slug' => 'bole-medhanialem-luxury-penthouse',
        'price' => 10000000.00,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 240,
        'type' => 'sale',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'address' => 'Near Medhanialem Cathedral',
        'agent_id' => $this->listingAgent->id,
    ]);
});

test('agent tier and effective split percentages resolve correctly', function () {
    expect($this->listingAgent->tier)->toBe('junior');
    expect($this->listingAgent->effective_split_percentage)->toBe(60.0);
    expect($this->listingAgent->tier_details['name'])->toBe('Junior Associate');

    expect($this->sellingAgent->tier)->toBe('senior');
    expect($this->sellingAgent->effective_split_percentage)->toBe(70.0);
    expect($this->sellingAgent->tier_details['name'])->toBe('Senior Consultant');

    expect($this->referralAgent->tier)->toBe('principal');
    expect($this->referralAgent->effective_split_percentage)->toBe(80.0);
    expect($this->referralAgent->tier_details['name'])->toBe('Principal Partner');
});

test('admin can manage agent tier, split percentage, and licensing via WorkerManagementController', function () {
    $response = $this->actingAs($this->admin)->put(route('dashboard.agents.update', $this->listingAgent), [
        'name' => 'Almaz Elevated Agency',
        'email' => 'almaz@example.com',
        'phone' => '+251 91 100 2000',
        'tier' => 'principal',
        'split_percentage' => 85.00,
        'license_number' => 'ETH-BRK-AA-2026-VIP',
        'license_verified' => true,
        'is_active' => true,
    ]);

    $response->assertSessionHasNoErrors();
    $this->listingAgent->refresh();

    expect($this->listingAgent->tier)->toBe('principal');
    expect((float) $this->listingAgent->split_percentage)->toBe(85.00);
    expect($this->listingAgent->license_number)->toBe('ETH-BRK-AA-2026-VIP');
    expect($this->listingAgent->license_verified)->toBeTrue();
});

test('CommissionSplitService computes single agent commission with ERCA 2% withholding tax', function () {
    $service = new CommissionSplitService;

    // 10,000,000 ETB property @ 2% commission rate = 200,000 ETB total pool
    // Junior agent has 60% split -> 120,000 ETB gross
    // Agency retains 40% -> 80,000 ETB
    // 2% WHT on 120,000 ETB = 2,400 ETB
    // Net payout = 117,600 ETB
    $splits = $service->calculateSplits(
        dealPrice: 10000000.00,
        listingAgent: $this->listingAgent,
        commissionRate: 2.00
    );

    expect($splits['total_commission_pool'])->toBe(200000.00);
    expect($splits['split_type'])->toBe('single_agent');
    expect($splits['company_retained'])->toBe(80000.00);

    expect($splits['listing_agent']['gross_commission'])->toBe(120000.00);
    expect($splits['listing_agent']['withholding_tax'])->toBe(2400.00);
    expect($splits['listing_agent']['net_payout'])->toBe(117600.00);
    expect($splits['selling_agent'])->toBeNull();
    expect($splits['referral_agent'])->toBeNull();
});

test('CommissionSplitService computes co-brokered deal split between listing and selling agents', function () {
    $service = new CommissionSplitService;

    // 10,000,000 ETB deal @ 2% = 200,000 ETB pool
    // Listing side 50% = 100,000 ETB pool -> Junior 60% = 60,000 ETB gross, Agency retains 40,000 ETB
    // Selling side 50% = 100,000 ETB pool -> Senior 70% = 70,000 ETB gross, Agency retains 30,000 ETB
    // Total company retained = 40,000 + 30,000 = 70,000 ETB
    // Listing Agent WHT (2% of 60,000) = 1,200 ETB -> Net 58,800 ETB
    // Selling Agent WHT (2% of 70,000) = 1,400 ETB -> Net 68,600 ETB
    $splits = $service->calculateSplits(
        dealPrice: 10000000.00,
        listingAgent: $this->listingAgent,
        sellingAgent: $this->sellingAgent,
        commissionRate: 2.00
    );

    expect($splits['total_commission_pool'])->toBe(200000.00);
    expect($splits['split_type'])->toBe('co_brokered');
    expect($splits['company_retained'])->toBe(70000.00);

    expect($splits['listing_agent']['gross_commission'])->toBe(60000.00);
    expect($splits['listing_agent']['withholding_tax'])->toBe(1200.00);
    expect($splits['listing_agent']['net_payout'])->toBe(58800.00);

    expect($splits['selling_agent']['gross_commission'])->toBe(70000.00);
    expect($splits['selling_agent']['withholding_tax'])->toBe(1400.00);
    expect($splits['selling_agent']['net_payout'])->toBe(68600.00);
});

test('CommissionSplitService deducts referral bonus before split', function () {
    $service = new CommissionSplitService;

    // 10,000,000 ETB deal @ 2% = 200,000 ETB pool
    // 10% referral cut = 20,000 ETB -> Referral WHT (2%) = 400 ETB -> Net = 19,600 ETB
    // Remaining pool = 180,000 ETB
    // Listing Agent Junior 60% of 180,000 = 108,000 ETB gross -> WHT = 2,160 ETB -> Net = 105,840 ETB
    // Company retains 40% of 180,000 = 72,000 ETB
    $splits = $service->calculateSplits(
        dealPrice: 10000000.00,
        listingAgent: $this->listingAgent,
        referralAgent: $this->referralAgent,
        commissionRate: 2.00,
        referralPercent: 10.00
    );

    expect($splits['total_commission_pool'])->toBe(200000.00);
    expect($splits['split_type'])->toBe('referral');
    expect($splits['company_retained'])->toBe(72000.00);

    expect($splits['referral_agent']['gross_commission'])->toBe(20000.00);
    expect($splits['referral_agent']['withholding_tax'])->toBe(400.00);
    expect($splits['referral_agent']['net_payout'])->toBe(19600.00);

    expect($splits['listing_agent']['gross_commission'])->toBe(108000.00);
    expect($splits['listing_agent']['withholding_tax'])->toBe(2160.00);
    expect($splits['listing_agent']['net_payout'])->toBe(105840.00);
});

test('recordDealCommission persists multi-tier commission record correctly', function () {
    $service = new CommissionSplitService;

    $commission = $service->recordDealCommission(
        property: $this->property,
        listingAgent: $this->listingAgent,
        dealPrice: 10000000.00,
        sellingAgent: $this->sellingAgent,
        referralAgent: $this->referralAgent,
        commissionRate: 2.00
    );

    expect($commission)->toBeInstanceOf(Commission::class);
    expect($commission->split_type)->toBe('co_brokered');
    expect((float) $commission->total_deal_commission)->toBe(200000.00);
    expect($commission->listing_agent_id)->toBe($this->listingAgent->id);
    expect($commission->selling_agent_id)->toBe($this->sellingAgent->id);
    expect($commission->referral_agent_id)->toBe($this->referralAgent->id);
    expect($commission->isCoBrokered())->toBeTrue();
    expect($commission->hasReferral())->toBeTrue();
    expect((float) $commission->company_retained_amount)->toBeGreaterThan(0);
});

test('co-brokered and referral agents can view their commission voucher but unauthorized users cannot', function () {
    $service = new CommissionSplitService;

    $commission = $service->recordDealCommission(
        property: $this->property,
        listingAgent: $this->listingAgent,
        dealPrice: 10000000.00,
        sellingAgent: $this->sellingAgent,
        referralAgent: $this->referralAgent
    );

    // Listing agent can access
    $this->actingAs($this->listingAgentUser)
        ->get(route('commissions.voucher', $commission))
        ->assertOk()
        ->assertSee('AGENT COMMISSION DISBURSEMENT VOUCHER')
        ->assertSee('Multi-Party Brokerage Split Hierarchy');

    // Co-brokered selling agent can access
    $this->actingAs($this->sellingAgentUser)
        ->get(route('commissions.voucher', $commission))
        ->assertOk()
        ->assertSee('AGENT COMMISSION DISBURSEMENT VOUCHER')
        ->assertSee('Dawit Realty Group');

    // Referral agent can access
    $this->actingAs($this->referralAgentUser)
        ->get(route('commissions.voucher', $commission))
        ->assertOk()
        ->assertSee('Bethlehem Consulting');

    // Unrelated third-party agent cannot access
    $otherUser = User::create([
        'name' => 'Other Agent',
        'email' => 'other@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);
    Agent::create([
        'user_id' => $otherUser->id,
        'name' => 'Other Agency',
        'email' => 'other@example.com',
        'is_active' => true,
    ]);

    $this->actingAs($otherUser)
        ->get(route('commissions.voucher', $commission))
        ->assertForbidden();
});

test('finance dashboard renders agency retained commissions and co-brokered breakdown', function () {
    $service = new CommissionSplitService;

    $service->recordDealCommission(
        property: $this->property,
        listingAgent: $this->listingAgent,
        dealPrice: 10000000.00,
        sellingAgent: $this->sellingAgent
    );

    $response = $this->actingAs($this->admin)->get(route('finance.index'));

    $response->assertOk();
    $response->assertSee('Agency Retained Comm.');
    $response->assertSee('Co-Brokered');
    $response->assertSee('Bole Medhanialem Luxury Penthouse');
});

test('agent portal displays agent tier and includes co-brokered commissions in commissions desk', function () {
    $service = new CommissionSplitService;

    // Commission where sellingAgent is the co-broker
    $commission = $service->recordDealCommission(
        property: $this->property,
        listingAgent: $this->listingAgent,
        dealPrice: 10000000.00,
        sellingAgent: $this->sellingAgent
    );

    $response = $this->actingAs($this->sellingAgentUser)->get(route('agent.portal'));

    $response->assertOk();
    $response->assertSee('Senior Consultant');
    $response->assertSee('70% Split Tier');
    $response->assertSee('ETH-BRK-AA-2024-002');
    $response->assertSee('Co-Broker');
});

test('agent portal renders profile edit button at top near notification and removes it from sidebar navbar', function () {
    $response = $this->actingAs($this->sellingAgentUser)->get(route('agent.portal'));

    $response->assertOk();
    $response->assertSee('Edit Profile');
    $response->assertSee('href="#profile"', false);
    $response->assertSee('data-target-section="profile"', false);

    // Verify logout button was removed from top header (it remains in the sidebar signed-in box)
    $content = $response->getContent();
    expect(substr_count($content, route('logout')))->toBeGreaterThanOrEqual(1);
});
