<?php

use App\Models\Agent;
use App\Models\Property;
use App\Models\User;

test('unauthenticated users cannot access analytics dashboard', function () {
    $this->get(route('dashboard.analytics'))
        ->assertRedirect(route('login'));

    $this->getJson(route('dashboard.analytics.data'))
        ->assertUnauthorized();
});

test('unauthorized users without admin role are forbidden from analytics', function () {
    $client = User::factory()->create([
        'role' => 'client',
    ]);

    $this->actingAs($client)
        ->get(route('dashboard.analytics'))
        ->assertForbidden();

    $this->actingAs($client)
        ->getJson(route('dashboard.analytics.data'))
        ->assertForbidden();
});

test('admin can access analytics dashboard and view all 8 kpis and charts', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('dashboard.analytics'));

    $response->assertOk();
    $response->assertSee('Analytics Dashboard');
    $response->assertSee('Total Users');
    $response->assertSee('Total Agents');
    $response->assertSee('Total Properties');
    $response->assertSee('Available Properties');
    $response->assertSee('Rented Properties');
    $response->assertSee('Sold Properties');
    $response->assertSee('Total Revenue');
    $response->assertSee('Total Commission');
    $response->assertSee('Monthly Trends: Property Sales, Rentals &amp; Revenue', false);
    $response->assertSee('Agent Performance &amp; Deal Closings', false);
    $response->assertSee('Property Inventory Breakdown');
    $response->assertSee('Rent vs. Sale Transactions');
    $response->assertSee('Geographic Distribution');
    $response->assertSee('Advanced Analytics: Property Price vs. Property Area');
    $response->assertSee('https://cdn.jsdelivr.net/npm/apexcharts');
});

test('admin can fetch json analytics data for all time', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('dashboard.analytics.data', ['period' => 'all']));

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'date_range' => [
                'period',
                'label',
            ],
            'data' => [
                'kpis' => [
                    'total_users',
                    'all_users_count',
                    'total_agents',
                    'all_agents_count',
                    'total_properties',
                    'all_properties_count',
                    'available_properties',
                    'rented_properties',
                    'sold_properties',
                    'total_revenue',
                    'total_commission',
                ],
                'monthly_trends' => [
                    'categories',
                    'sales',
                    'rentals',
                    'revenue',
                    'commission',
                ],
                'agent_performance' => [
                    'agents',
                    'sold',
                    'rented',
                    'totals',
                    'details',
                ],
                'property_status' => [
                    'series',
                    'labels',
                    'counts' => [
                        'available',
                        'rented',
                        'sold',
                    ],
                    'totals',
                    'percentages',
                ],
                'rent_vs_sale' => [
                    'categories',
                    'sale_deals',
                    'rent_deals',
                ],
                'locations' => [
                    'cities',
                    'counts',
                    'raw',
                ],
                'scatter' => [
                    'sale_points',
                    'rent_points',
                    'total_points',
                ],
            ],
        ]);

    expect($response->json('success'))->toBeTrue();
    expect($response->json('data.kpis.total_properties'))->toBeGreaterThanOrEqual(0);
});

test('admin can filter analytics data by periods and custom date range', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    // Test 'today'
    $todayRes = $this->actingAs($admin)
        ->getJson(route('dashboard.analytics.data', ['period' => 'today']));
    $todayRes->assertOk();
    expect($todayRes->json('date_range.period'))->toBe('today');

    // Test 'week'
    $weekRes = $this->actingAs($admin)
        ->getJson(route('dashboard.analytics.data', ['period' => 'week']));
    $weekRes->assertOk();
    expect($weekRes->json('date_range.period'))->toBe('week');

    // Test 'month'
    $monthRes = $this->actingAs($admin)
        ->getJson(route('dashboard.analytics.data', ['period' => 'month']));
    $monthRes->assertOk();
    expect($monthRes->json('date_range.period'))->toBe('month');

    // Test 'year'
    $yearRes = $this->actingAs($admin)
        ->getJson(route('dashboard.analytics.data', ['period' => 'year']));
    $yearRes->assertOk();
    expect($yearRes->json('date_range.period'))->toBe('year');

    // Test 'custom'
    $customRes = $this->actingAs($admin)
        ->getJson(route('dashboard.analytics.data', [
            'period' => 'custom',
            'from' => '2026-01-01',
            'to' => '2026-12-31',
        ]));
    $customRes->assertOk();
    expect($customRes->json('date_range.period'))->toBe('custom');
});

test('scatter chart includes properties with valid price and area', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $property = Property::create([
        'title' => 'Test Analytics Villa',
        'slug' => 'test-analytics-villa-'.uniqid(),
        'price' => 7500000,
        'area' => 350,
        'type' => 'sale',
        'city' => 'Addis Ababa',
        'status' => 'available',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('dashboard.analytics.data', ['period' => 'all']));

    $response->assertOk();
    $salePoints = collect($response->json('data.scatter.sale_points'));
    $matching = $salePoints->firstWhere('name', 'Test Analytics Villa');

    expect($matching)->not->toBeNull();
    expect((float) $matching['x'])->toBe(350.0);
    expect((float) $matching['y'])->toBe(7500000.0);
    expect($matching['city'])->toBe('Addis Ababa');

    // Clean up
    $property->delete();
});

test('admin dashboard overview displays embedded apexcharts', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('admin-overview-monthly-chart');
    $response->assertSee('admin-overview-status-chart');
    $response->assertSee('admin-overview-agent-chart');
    $response->assertSee('admin-overview-location-chart');
    $response->assertSee('Executive Intelligence &amp; Performance Overview', false);
});

test('finance desk overview displays embedded apexcharts', function () {
    $financeOfficer = User::factory()->create([
        'role' => 'finance_officer',
    ]);

    $response = $this->actingAs($financeOfficer)
        ->get(route('finance.index'));

    $response->assertOk();
    $response->assertSee('finance-cashflow-chart');
    $response->assertSee('finance-categories-chart');
    $response->assertSee('Financial Analytics &amp; Capital Flow Intelligence', false);
});

test('agent portal overview displays embedded apexcharts', function () {
    $agentUser = User::factory()->create([
        'role' => 'agent',
    ]);
    $agent = Agent::create([
        'user_id' => $agentUser->id,
        'name' => 'Test Agent Portal',
        'email' => $agentUser->email,
        'is_active' => true,
    ]);

    $response = $this->actingAs($agentUser)
        ->get(route('agent.portal'));

    $response->assertOk();
    $response->assertSee('agent-property-status-chart');
    $response->assertSee('agent-commission-chart');
    $response->assertSee('Agent Performance Intelligence');
});
