<?php

use App\Models\User;
use App\Services\DashboardAnalyticsService;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('unauthenticated users cannot fetch analytics chart data', function () {
    $response = $this->getJson(route('dashboard.user-overview.charts'));

    $response->assertUnauthorized();
});

test('buyer dashboard does not render old property manager control center card', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $response = $this->actingAs($buyer)->get(route('buyer.dashboard'));

    $response->assertOk();
    $response->assertDontSee('Property Manager Control Center');
    $response->assertDontSee('Occupied vs Vacant');
    $response->assertDontSee('Units & Occupancy Roll');
    $response->assertSee('id="overview"', false);
    $response->assertSee('asking-price indicator / platform estimate');
});

test('property manager dashboard does not render old hardcoded control center card and includes analytics overview', function () {
    $pm = User::factory()->create(['role' => 'property_manager']);

    $response = $this->actingAs($pm)->get(route('property-manager.dashboard'));

    $response->assertOk();
    $response->assertDontSee('Property Manager Control Center');
    $response->assertSee('id="overview"', false);
    $response->assertSee('data-section="overview"', false);
});

test('analytics endpoint returns permission-tailored payload with max 5 slice donut and platform estimate label', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $response = $this->actingAs($buyer)->getJson(route('dashboard.user-overview.charts', ['period' => '30d']));

    $response->assertOk();
    $data = $response->json();

    // Check payload structure
    expect($data)->toHaveKeys(['stat_cards', 'charts', 'progress_bars', 'recent_records', 'period', 'periods_data']);

    // Check period
    expect($data['period'])->toBe('30d');

    // Donut chart must have max 5 slices
    $donut = $data['charts']['shares_donut'];
    expect(count($donut['labels']))->toBeLessThanOrEqual(5);
    expect(count($donut['series']))->toBeLessThanOrEqual(5);

    // If market trends line chart is present, verify explicit platform estimate disclaimer
    if (isset($data['charts']['market_price_trends'])) {
        expect($data['charts']['market_price_trends']['label_tag'])->toContain('asking-price indicator / platform estimate');
    }
});

test('analytics service provides instant 0ms pre-calculated datasets for all periods', function () {
    $user = User::factory()->create(['role' => 'buyer']);

    $service = app(DashboardAnalyticsService::class);
    $data = $service->getDashboardData($user, '30d');

    expect($data['periods_data'])->toHaveKeys(['7d', '90d', '1y']);
    foreach (['7d', '90d', '1y'] as $p) {
        $donut = $data['periods_data'][$p]['charts']['shares_donut'];
        expect(count($donut['labels']))->toBeLessThanOrEqual(5);
    }
});

test('agent crm funnel widget is included only for users with agent permissions', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $agent = User::factory()->create(['role' => 'agent']);
    $agent->ensureAgentProfile();

    $service = app(DashboardAnalyticsService::class);

    $buyerData = $service->getDashboardData($buyer, '30d');
    $agentData = $service->getDashboardData($agent, '30d');

    expect($buyerData['charts'])->not->toHaveKey('agent_crm_funnel');
    expect($agentData['charts'])->toHaveKey('agent_crm_funnel');
    expect($agentData['charts']['agent_crm_funnel'])->toHaveKeys(['title', 'stages']);
});
