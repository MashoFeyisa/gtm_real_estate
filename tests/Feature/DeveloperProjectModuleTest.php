<?php

use App\Models\Agent;
use App\Models\ConstructionMilestone;
use App\Models\DeveloperPaymentPlan;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectUnit;
use App\Models\UnitReservation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->adminUser = User::create([
        'name' => 'System Admin',
        'email' => 'admin.dev@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->agentUser = User::create([
        'name' => 'Developer Partner Agent',
        'email' => 'partner.agent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Ethio Premier Developers Agency',
        'email' => 'ethiopremier@example.com',
        'phone' => '+251 91 555 4433',
        'is_active' => true,
    ]);

    $this->project = Project::create([
        'project_code' => 'ETH-DEV-AA-2026-BOLE',
        'name' => 'Bole Horizon Luxury Residences',
        'slug' => 'bole-horizon-luxury-residences',
        'developer_name' => 'Noah Real Estate & Construction',
        'developer_phone' => '+251 91 100 9988',
        'developer_email' => 'sales@noah-ethiopia.et',
        'agent_id' => $this->agent->id,
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'address' => 'Near Edna Mall, Cameroon Street, Bole',
        'project_type' => 'mixed_use',
        'construction_status' => 'under_construction',
        'overall_completion_percentage' => 58,
        'expected_completion_date' => 'Q4 2027',
        'land_area_sqm' => 5000.00,
        'total_buildings_count' => 2,
        'total_units_count' => 50,
        'starting_price' => 14000000.00,
        'currency' => 'ETB',
        'description' => 'A landmark luxury off-plan development with high-speed elevators, 3-phase power, standby generators, and underground resident parking.',
        'facilities' => [
            '3-Phase Industrial Electric & 500kVA Generator',
            '200,000L Reservoir Tank',
            '4 High-Speed Elevators',
            'Underground Assigned Parking',
        ],
        'is_featured' => true,
        'status' => 'published',
    ]);

    $this->building = ProjectBuilding::create([
        'project_id' => $this->project->id,
        'name' => 'Tower A - Sunrise Vista',
        'total_floors' => 'G+15',
        'units_count' => 25,
    ]);

    $this->unit1 = ProjectUnit::create([
        'project_id' => $this->project->id,
        'building_id' => $this->building->id,
        'unit_number' => 'A-502',
        'floor_number' => '5',
        'unit_type' => '2_bedroom',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area_sqm' => 120.00,
        'price' => 14000000.00,
        'currency' => 'ETB',
        'orientation' => 'Sunrise East / Bole View',
        'parking_spaces' => 1,
        'status' => 'available',
    ]);

    $this->unit2 = ProjectUnit::create([
        'project_id' => $this->project->id,
        'building_id' => $this->building->id,
        'unit_number' => 'A-1201',
        'floor_number' => '12',
        'unit_type' => '3_bedroom',
        'bedrooms' => 3,
        'bathrooms' => 3,
        'area_sqm' => 180.00,
        'price' => 21000000.00,
        'currency' => 'ETB',
        'orientation' => 'Panoramic City View',
        'parking_spaces' => 2,
        'status' => 'available',
    ]);

    // Milestones conforming to SRS §93
    $this->milestone1 = ConstructionMilestone::create([
        'project_id' => $this->project->id,
        'milestone_name' => 'Excavation & Foundation',
        'percentage' => 100,
        'target_date' => 'Completed',
        'status' => 'completed',
        'order_index' => 1,
    ]);

    $this->milestone2 = ConstructionMilestone::create([
        'project_id' => $this->project->id,
        'milestone_name' => 'Structural Skeleton',
        'percentage' => 70,
        'target_date' => 'In Progress',
        'status' => 'in_progress',
        'order_index' => 2,
    ]);

    // Payment plan conforming to SRS §94
    $this->paymentPlan1 = DeveloperPaymentPlan::create([
        'project_id' => $this->project->id,
        'installment_name' => '10% Reservation & Booking Deposit',
        'percentage' => 10.00,
        'milestone_trigger' => 'Upon Contract Signing',
        'order_index' => 1,
    ]);

    $this->paymentPlan2 = DeveloperPaymentPlan::create([
        'project_id' => $this->project->id,
        'installment_name' => '20% Foundation Milestone',
        'percentage' => 20.00,
        'milestone_trigger' => 'Foundation Signed-Off by City Inspector',
        'order_index' => 2,
    ]);
});

test('public developments directory displays projects and filter capabilities', function () {
    $response = $this->get(route('developments.index'));

    $response->assertStatus(200);
    $response->assertSee('Master Projects');
    $response->assertSee('Developer Units');
    $response->assertSee('Bole Horizon Luxury Residences');
    $response->assertSee('Noah Real Estate &amp; Construction', false);
    $response->assertSee('Progress: 58%');
    $response->assertSee('ETB 14,000,000');
    $response->assertSee('View Units');
});

test('project showcase page displays construction milestones, payment schedule, and unit inventory', function () {
    $response = $this->get(route('developments.show', $this->project->slug));

    $response->assertStatus(200);
    $response->assertSee('Bole Horizon Luxury Residences');
    $response->assertSee('ETH-DEV-AA-2026-BOLE');

    // Milestones (SRS §93)
    $response->assertSee('Construction Progress & Milestones');
    $response->assertSee('Excavation & Foundation');
    $response->assertSee('Structural Skeleton');

    // Payment Plans (SRS §94)
    $response->assertSee('Developer Milestone Payment Schedule');
    $response->assertSee('10% Reservation & Booking Deposit');
    $response->assertSee('20% Foundation Milestone');

    // Unit Inventory Matrix (SRS §91, §92)
    $response->assertSee('Unit Inventory &amp; Availability Matrix', false);
    $response->assertSee('Unit A-502');
    $response->assertSee('Floor 5');
    $response->assertSee('120 sqm');
    $response->assertSee('Reserve Unit');
});

test('prospective buyer can successfully reserve an off-plan unit', function () {
    $client = User::factory()->create(['role' => 'client']);
    $payload = [
        'buyer_name' => 'Almaz Wolde',
        'buyer_email' => 'almaz@example.com',
        'buyer_phone' => '+251 91 888 7766',
        'notes' => 'Looking for financing support via Ethiopian Commercial Bank.',
    ];

    $response = $this->actingAs($client)->post(route('developments.reserve-unit', [
        'project' => $this->project->id,
        'unit' => $this->unit1->id,
    ]), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('developer_reservation_success');

    // Unit status transitions to reserved
    expect($this->unit1->fresh()->status)->toBe('reserved');

    // Reservation record created with reference
    $reservation = UnitReservation::where('unit_id', $this->unit1->id)->first();
    expect($reservation)->not->toBeNull()
        ->and($reservation->reservation_reference)->toStartWith('RES-DEV-AA-')
        ->and($reservation->user_id)->toBe($client->id)
        ->and($reservation->buyer_name)->toBe('Almaz Wolde')
        ->and($reservation->buyer_phone)->toBe('+251 91 888 7766')
        ->and($reservation->status)->toBe('pending');
});

test('guest must sign in before reserving an off-plan unit', function () {
    $response = $this->post(route('developments.reserve-unit', [
        'project' => $this->project->id,
        'unit' => $this->unit1->id,
    ]));

    $response->assertRedirect(route('register'))
        ->assertSessionHas('signup_prompt', 'Register / Login to continue: Create a client account or sign in before reserving a development unit.');

    expect($this->unit1->fresh()->status)->toBe('available')
        ->and(UnitReservation::count())->toBe(0);
});

test('admin can view developments in dashboard and update unit status', function () {
    // Admin views dashboard
    $response = $this->actingAs($this->adminUser)->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Developments');
    $response->assertSee('Off-Plan');
    $response->assertDontSee('Module 3');
    $response->assertSee('Bole Horizon Luxury Residences');
    $response->assertSee('ETH-DEV-AA-2026-BOLE');

    // Admin updates unit status to contracted
    $updateResponse = $this->actingAs($this->adminUser)->put(route('developments.update-unit-status', $this->unit1), [
        'status' => 'contracted',
    ]);

    $updateResponse->assertRedirect();
    $updateResponse->assertSessionHas('success');

    expect($this->unit1->fresh()->status)->toBe('contracted');
});

test('admin can update construction milestone percentage and recalculate project overall progress', function () {
    // Update milestone2 from 70% to 100%
    $response = $this->actingAs($this->adminUser)->put(route('developments.update-milestone', [
        'project' => $this->project->id,
        'milestone' => $this->milestone2->id,
    ]), [
        'percentage' => 100,
        'status' => 'completed',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($this->milestone2->fresh()->percentage)->toBe(100);
    expect($this->milestone2->fresh()->status)->toBe('completed');

    // Overall completion percentage was recalculated as average of milestone1 (100) and milestone2 (100) = 100
    expect($this->project->fresh()->overall_completion_percentage)->toBe(100);
});

test('admin can post a new development project with default starter milestones', function () {
    $payload = [
        'name' => 'CMC Palm Residency Complex',
        'developer_name' => 'Flintstone Engineering',
        'developer_phone' => '+251 91 122 3344',
        'developer_email' => 'cmc@flintstone.et',
        'city' => 'Addis Ababa',
        'subcity' => 'Yeka',
        'address' => 'Near CMC St. Michael',
        'project_type' => 'apartments',
        'construction_status' => 'under_construction',
        'overall_completion_percentage' => 35,
        'expected_completion_date' => 'Q1 2028',
        'starting_price' => 9800000.00,
        'land_area_sqm' => 4000.00,
        'total_buildings_count' => 3,
        'description' => 'Modern family apartments surrounded by green parks.',
        'facilities_input' => "Backup Generator\nWater Tanks\nElevators\n24/7 Security",
    ];

    $response = $this->actingAs($this->adminUser)->post(route('developments.store'), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $project = Project::where('name', 'CMC Palm Residency Complex')->first();
    expect($project)->not->toBeNull()
        ->and($project->project_code)->toStartWith('ETH-DEV-AA-')
        ->and((float) $project->starting_price)->toBe(9800000.0)
        ->and($project->city)->toBe('Addis Ababa')
        ->and($project->milestones()->count())->toBe(5)
        ->and($project->paymentPlans()->count())->toBe(6);
});

test('agent can post a new development project assigned to their agent profile', function () {
    $payload = [
        'name' => 'Sarbet Diplomatic Towers',
        'developer_name' => 'Sunshine Construction',
        'city' => 'Addis Ababa',
        'subcity' => 'Kirkos',
        'project_type' => 'mixed_use',
        'construction_status' => 'pre_construction',
        'overall_completion_percentage' => 10,
        'starting_price' => 16500000.00,
    ];

    $response = $this->actingAs($this->agentUser)->post(route('developments.store'), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $project = Project::where('name', 'Sarbet Diplomatic Towers')->first();
    expect($project)->not->toBeNull()
        ->and($project->agent_id)->toBe($this->agent->id);

    // Agent portal shows the project
    $portalResponse = $this->actingAs($this->agentUser)->get(route('agent.portal'));
    $portalResponse->assertStatus(200);
    $portalResponse->assertSee('Sarbet Diplomatic Towers');
    $portalResponse->assertSee('Sunshine Construction');
});

test('agent can add a unit to their development project inventory', function () {
    $payload = [
        'unit_number' => 'B-704',
        'floor_number' => '7',
        'unit_type' => '2_bedroom',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area_sqm' => 110.0,
        'price' => 13500000.0,
        'orientation' => 'South-West View',
        'parking_spaces' => 1,
        'status' => 'available',
    ];

    $response = $this->actingAs($this->agentUser)->post(route('developments.units.store', $this->project), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $unit = ProjectUnit::where('project_id', $this->project->id)->where('unit_number', 'B-704')->first();
    expect($unit)->not->toBeNull()
        ->and((float) $unit->price)->toBe(13500000.0)
        ->and($unit->floor_number)->toBe('7');
});

test('agent cannot edit or delete another agents development project', function () {
    $otherAgentUser = User::create([
        'name' => 'Other Agent User',
        'email' => 'other.agent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);
    Agent::create([
        'user_id' => $otherAgentUser->id,
        'name' => 'Competitor Realty',
        'email' => 'competitor@example.com',
        'phone' => '+251 92 000 9999',
        'is_active' => true,
    ]);

    // Unauthorized edit attempt
    $response = $this->actingAs($otherAgentUser)->put(route('developments.update', $this->project), [
        'name' => 'Hijacked Name',
        'developer_name' => 'Hijacker',
        'city' => 'Addis Ababa',
        'project_type' => 'apartments',
        'construction_status' => 'completed',
        'starting_price' => 50000,
    ]);
    $response->assertStatus(403);

    // Unauthorized delete attempt
    $deleteResponse = $this->actingAs($otherAgentUser)->delete(route('developments.destroy', $this->project));
    $deleteResponse->assertStatus(403);
});

test('admin can delete a development project', function () {
    $response = $this->actingAs($this->adminUser)->delete(route('developments.destroy', $this->project));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(Project::find($this->project->id))->toBeNull();
});

test('admin and owner agent can edit a development project', function () {
    $response = $this->actingAs($this->adminUser)->put(route('developments.update', $this->project), [
        'name' => 'Bole Horizon Luxury Residences Updated',
        'developer_name' => 'Flintstone & Noah Joint Venture',
        'developer_phone' => '+251 91 199 0011',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole Medhanialem',
        'project_type' => 'mixed_use',
        'construction_status' => 'near_completion',
        'overall_completion_percentage' => 85,
        'starting_price' => 14000000,
        'is_featured' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $updated = $this->project->fresh();
    expect($updated->name)->toBe('Bole Horizon Luxury Residences Updated');
    expect($updated->developer_name)->toBe('Flintstone & Noah Joint Venture');
    expect($updated->subcity)->toBe('Bole Medhanialem');
    expect($updated->project_type)->toBe('mixed_use');
    expect($updated->construction_status)->toBe('near_completion');
    expect($updated->overall_completion_percentage)->toBe(85);
    expect((float) $updated->starting_price)->toBe(14000000.0);
    expect($updated->is_featured)->toBeTrue();
});

test('homepage displays published development projects in featured projects section with working link to show page', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee('Developments');
    $response->assertSee('Featured Projects');
    $response->assertSee($this->project->name);
    $response->assertSee($this->project->developer_name);
    $response->assertSee(route('developments.show', $this->project->slug));
});
