<?php

use App\Models\Agent;
use App\Models\Campaign;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->adminUser = User::create([
        'name' => 'System Admin',
        'email' => 'admin.campaign.test@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->campaignOfficer = User::create([
        'name' => 'Campaign Officer Abebe',
        'email' => 'officer.campaign.test@example.com',
        'password' => Hash::make('password123'),
        'role' => 'campaign_officer',
    ]);

    $this->companyUser = User::create([
        'name' => 'GTM Real Estate Co',
        'email' => 'company.campaign.test@example.com',
        'password' => Hash::make('password123'),
        'role' => 'company',
    ]);

    $this->agentUser = User::create([
        'name' => 'Agent Chala',
        'email' => 'agent.campaign.test@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Agent Chala',
        'email' => 'agent.chala@example.com',
        'phone' => '+251911223344',
        'is_active' => true,
    ]);

    $this->property = Property::create([
        'property_code' => 'ETH-TEST-SALE-001',
        'title' => 'Bole Luxury Penthouse',
        'slug' => 'bole-luxury-penthouse-'.uniqid(),
        'type' => 'sale',
        'property_category' => 'apartment',
        'price' => 15000000,
        'city' => 'Addis Ababa',
        'agent_id' => $this->agent->id,
        'is_active' => true,
        'status' => 'published',
    ]);

    $this->campaign = Campaign::create([
        'agent_id' => $this->agent->id,
        'user_id' => $this->agentUser->id,
        'property_id' => $this->property->id,
        'title' => 'Exclusive Bole Penthouse Launch',
        'campaign_type' => 'featured_listing',
        'target_audience' => 'Diaspora Investors',
        'headline' => 'Luxury living in the heart of Addis Ababa',
        'description' => 'Unparalleled penthouse with panoramic mountain and city skyline views.',
        'budget' => 50000,
        'status' => 'under_review',
    ]);
});

test('campaigns desk dashboard route is removed and returns 404', function () {
    $response = $this->actingAs($this->adminUser)->get('/campaigns/desk');

    $response->assertStatus(404);
});

test('admin cannot update campaign status directly and receives 403 forbidden', function () {
    $response = $this->actingAs($this->adminUser)->patch(route('campaigns.update-status', $this->campaign->id), [
        'status' => 'approved',
    ]);

    $response->assertStatus(403);
});

test('campaign officer role is removed from admin user creation role selection and store validation', function () {
    $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertDontSee('value="campaign_officer"', false);
    $response->assertDontSee('Campaign Officer (Promotions &amp; Marketing Desk)', false);

    $storeResponse = $this->actingAs($this->adminUser)->post(route('dashboard.users.store'), [
        'name' => 'New Officer',
        'email' => 'new.officer@example.com',
        'password' => 'SecurePass123!',
        'role' => 'campaign_officer',
    ]);

    $storeResponse->assertSessionHasErrors(['role']);
});

test('campaign officer can manage and approve campaign status', function () {
    $response = $this->actingAs($this->campaignOfficer)->patch(route('campaigns.update-status', $this->campaign->id), [
        'status' => 'approved',
        'review_notes' => 'Approved for diaspora advertising campaign.',
    ]);

    $response->assertRedirect();
    $this->campaign->refresh();
    expect($this->campaign->status)->toBe('approved');
    expect($this->campaign->reviewed_by)->toBe($this->campaignOfficer->id);
});

test('transactions desk and payment desk render high-contrast print ledger button', function () {
    $response = $this->actingAs($this->companyUser)->get(route('company.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Print Ledger / መዝገብ አትም');
});

test('company dashboard does not render 7 core pillars buttons ribbon in front of company name', function () {
    $response = $this->actingAs($this->companyUser)->get(route('company.dashboard'));

    $response->assertStatus(200);
    $response->assertDontSee('7 Core Pillars');
    $response->assertDontSee('🏢 Branches');
    $response->assertDontSee('👔 Agents');
    $response->assertDontSee('🚨 Client Reports');
    $response->assertSee('GTM Real Estate Company');
});

test('company user is authorized to view financial audit statement without 403', function () {
    $response = $this->actingAs($this->companyUser)->get(route('finance.statement', ['period' => 'all']));

    $response->assertStatus(200);
    $response->assertSee('OFFICIAL FINANCIAL AUDIT STATEMENT');
});

test('property show page resolves via slug or numeric ID without 404 for privileged and campaign-linked properties', function () {
    // 1. By slug
    $slugResponse = $this->actingAs($this->companyUser)->get(route('properties.show', $this->property->slug));
    $slugResponse->assertStatus(200);
    $slugResponse->assertSee($this->property->title);

    // 2. By ID
    $idResponse = $this->actingAs($this->companyUser)->get(route('properties.show', $this->property->id));
    $idResponse->assertStatus(200);
    $idResponse->assertSee($this->property->title);
});

test('client portal renders active promotional campaigns and all marketplace properties', function () {
    // Publish the test campaign
    $this->campaign->update(['status' => 'published']);

    $response = $this->actingAs($this->companyUser)->get(route('client.portal'));

    $response->assertStatus(200);
    $response->assertSee('Special Campaigns');
    $response->assertSee('Exclusive Bole Penthouse Launch');
    $response->assertSee('All Properties');
});

test('managing campaign updates budget, audience, and auto-activates target property', function () {
    $this->property->update(['is_active' => false, 'status' => 'draft']);

    $response = $this->actingAs($this->campaignOfficer)->patch(route('campaigns.update-status', $this->campaign->id), [
        'status' => 'published',
        'budget' => 75000,
        'target_audience' => 'Diaspora High-Net-Worth',
        'review_notes' => 'Campaign approved and published to live market.',
    ]);

    $response->assertRedirect();
    $this->campaign->refresh();
    $this->property->refresh();

    expect($this->campaign->status)->toBe('published');
    expect((float) $this->campaign->budget)->toBe(75000.0);
    expect($this->campaign->target_audience)->toBe('Diaspora High-Net-Worth');
    expect($this->property->is_active)->toBeTrue();
    expect($this->property->status)->toBe('available');
});
