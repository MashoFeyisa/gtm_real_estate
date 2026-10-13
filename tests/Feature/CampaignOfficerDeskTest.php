<?php

use App\Models\Agent;
use App\Models\Campaign;
use App\Models\Property;
use App\Models\User;
use App\Services\WorkflowIntegrationService;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->officer = User::factory()->create(['role' => 'campaign_officer']);

    $this->agentUser = User::factory()->create(['role' => 'agent']);
    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Desk Test Agent',
        'email' => 'desk.campaign.agent@example.com',
        'phone' => '+251911000099',
        'is_active' => true,
    ]);

    $this->property = Property::create([
        'property_code' => 'ETH-CMP-PROP-001',
        'title' => 'Bole Ramadan Campaign Villa',
        'slug' => 'bole-ramadan-campaign-villa',
        'type' => 'sale',
        'property_category' => 'villa',
        'price' => 18500000,
        'city' => 'Addis Ababa',
        'agent_id' => $this->agent->id,
        'is_active' => true,
        'status' => 'published',
    ]);

    $this->campaign = Campaign::create([
        'agent_id' => $this->agent->id,
        'user_id' => $this->agentUser->id,
        'property_id' => $this->property->id,
        'title' => 'Bole Ramadan Promo',
        'campaign_type' => 'seasonal_discount',
        'target_audience' => 'Diaspora buyers',
        'headline' => 'Exclusive luxury living in Bole',
        'description' => 'Special 5% discount for early closers during Ramadan.',
        'budget' => 25000,
        'status' => 'under_review',
    ]);
});

test('campaign-office dashboard route is removed and returns 404', function () {
    $this->actingAs($this->officer)
        ->get('/campaign-office')
        ->assertNotFound();

    $this->get('/campaign-office')
        ->assertNotFound();
});

test('campaign officer sign in redirects to dedicated campaign dashboard', function () {
    $user = User::factory()->create([
        'role' => 'campaign_officer',
        'password' => Hash::make('CampaignPass123!'),
    ]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'CampaignPass123!',
    ])->assertRedirect(route('campaign.dashboard'));
});

test('campaign officer dashboard url links to campaign dashboard', function () {
    expect($this->officer->dashboard_url)->toBe(route('campaign.dashboard'));
});

test('campaign officer can access campaign dashboard and view queues', function () {
    $response = $this->actingAs($this->officer)->get(route('campaign.dashboard'));

    $response->assertOk();
    $response->assertSee('Campaign Desk');
    $response->assertSee('Editorial Review Queue');
});

test('actionable tasks for officer return empty when desk is decommissioned', function () {
    $tasks = app(WorkflowIntegrationService::class)->getActorActionableTasks($this->officer);

    expect($tasks)->toBeEmpty();
});

test('the officer can approve a campaign via status update endpoint', function () {
    $this->actingAs($this->officer)
        ->patch(route('campaigns.update-status', $this->campaign->id), [
            'status' => 'approved',
            'review_notes' => 'Meets platform marketing standards.',
        ])
        ->assertRedirect();

    $this->campaign->refresh();
    expect($this->campaign->status)->toBe('approved')
        ->and($this->campaign->reviewed_by)->toBe($this->officer->id);
});

test('running the database seeder provisions a campaign officer account', function () {
    $this->seed();

    $officer = User::where('role', 'campaign_officer')
        ->where('email', 'campaign@realestate.com')
        ->first();

    expect($officer)->not->toBeNull();
});
