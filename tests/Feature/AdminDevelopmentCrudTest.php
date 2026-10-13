<?php

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->adminUser = User::create([
        'name' => 'System Admin',
        'email' => 'admin.devcrud.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->regularUser = User::create([
        'name' => 'Buyer Client',
        'email' => 'buyer.devcrud.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->project = Project::create([
        'project_code' => 'ETH-TEST-DEV-'.uniqid(),
        'name' => 'Test Heights Luxury',
        'slug' => 'test-heights-luxury-'.uniqid(),
        'developer_name' => 'Apex Ethiopia Developers',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'address' => 'Cameroon St, Bole',
        'project_type' => 'apartments',
        'construction_status' => 'under_construction',
        'overall_completion_percentage' => 35,
        'starting_price' => 7500000.00,
        'currency' => 'ETB',
        'total_buildings_count' => 1,
        'total_units_count' => 0,
        'status' => 'published',
    ]);

    ProjectBuilding::create([
        'project_id' => $this->project->id,
        'name' => 'Tower A',
        'total_floors' => 'G+10',
        'units_count' => 0,
    ]);
});

test('admin dashboard renders developments table with actions and activity audit trail', function () {
    // Seed an audit log for this project
    AuditLog::log(
        action: 'development.created',
        entity: $this->project,
        newValue: ['name' => $this->project->name],
        reason: "Development project '{$this->project->name}' created"
    );

    $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Developments &amp; Projects', false);
    $response->assertSee('+ Add Development');
    $response->assertSee('Test Heights Luxury');
    $response->assertSee('+ Unit');
    $response->assertSee('Development CRUD Activity &amp; Logs', false);
    $response->assertSee('development.created');
});

test('admin can create a development project from dashboard and it logs audit activity', function () {
    $payload = [
        'name' => 'Sarbet Premium Villas',
        'developer_name' => 'Eleni Real Estate PLC',
        'developer_phone' => '+251 911 889900',
        'developer_email' => 'info@eleni.et',
        'city' => 'Addis Ababa',
        'subcity' => 'Sarbet',
        'address' => 'Near African Union HQ',
        'project_type' => 'luxury_villas',
        'construction_status' => 'pre_construction',
        'overall_completion_percentage' => 10,
        'expected_completion_date' => 'Q2 2028',
        'starting_price' => 18000000,
        'land_area_sqm' => 4500,
        'description' => 'Ultra high end gated villa community.',
        'facilities_input' => "24/7 Patrol Security\nSolar Backup Power\nClubhouse",
        'is_featured' => 1,
        'from_dashboard' => 1,
    ];

    $response = $this->actingAs($this->adminUser)->post(route('developments.store'), $payload);

    $response->assertRedirect(route('dashboard').'#projects');
    $this->assertDatabaseHas('projects', [
        'name' => 'Sarbet Premium Villas',
        'developer_name' => 'Eleni Real Estate PLC',
        'project_type' => 'luxury_villas',
    ]);

    $createdProject = Project::where('name', 'Sarbet Premium Villas')->first();
    expect($createdProject)->not->toBeNull();

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'development.created',
        'entity_type' => Project::class,
        'entity_id' => (string) $createdProject->id,
    ]);
});

test('admin can update a development project and change is logged in audit log', function () {
    $payload = [
        'name' => 'Test Heights Luxury (Phase 2 Updated)',
        'developer_name' => 'Apex Ethiopia Developers Ltd',
        'developer_phone' => '+251 922 334455',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole Medhanialem',
        'address' => 'Cameroon St, Bole',
        'project_type' => 'apartments',
        'construction_status' => 'near_completion',
        'overall_completion_percentage' => 85,
        'starting_price' => 9200000,
        'expected_completion_date' => 'Dec 2026',
        'description' => 'Updated luxury residences with premium marble fittings.',
        'from_dashboard' => 1,
    ];

    $response = $this->actingAs($this->adminUser)->put(route('developments.update', $this->project), $payload);

    $response->assertRedirect(route('dashboard').'#projects');
    $this->project->refresh();

    expect($this->project->name)->toBe('Test Heights Luxury (Phase 2 Updated)')
        ->and($this->project->construction_status)->toBe('near_completion')
        ->and($this->project->overall_completion_percentage)->toBe(85);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'development.updated',
        'entity_type' => Project::class,
        'entity_id' => (string) $this->project->id,
    ]);
});

test('admin can add a unit to development inventory from dashboard', function () {
    $payload = [
        'unit_number' => 'TH-401',
        'floor_number' => '4th Floor',
        'unit_type' => '2_bedroom',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area_sqm' => 118.5,
        'price' => 8400000,
        'orientation' => 'Sunrise Facing Bole Ridge',
        'parking_spaces' => 1,
        'status' => 'available',
        'from_dashboard' => 1,
    ];

    $response = $this->actingAs($this->adminUser)->post(route('developments.units.store', $this->project), $payload);

    $response->assertRedirect(route('dashboard').'#projects');
    $this->assertDatabaseHas('project_units', [
        'project_id' => $this->project->id,
        'unit_number' => 'TH-401',
        'unit_type' => '2_bedroom',
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'development.unit_created',
        'entity_type' => Project::class,
        'entity_id' => (string) $this->project->id,
    ]);
});

test('admin can delete a development project from dashboard', function () {
    $projectId = $this->project->id;
    $projectName = $this->project->name;

    $response = $this->actingAs($this->adminUser)->delete(route('developments.destroy', $this->project), [
        'from_dashboard' => 1,
    ]);

    $response->assertRedirect(route('dashboard').'#projects');
    $this->assertDatabaseMissing('projects', ['id' => $projectId]);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'development.deleted',
        'entity_type' => Project::class,
        'entity_id' => (string) $projectId,
    ]);
});

test('non-admin regular user cannot create, update, or delete developments', function () {
    $storeResponse = $this->actingAs($this->regularUser)->post(route('developments.store'), [
        'name' => 'Unauthorized Tower',
        'developer_name' => 'Bad Actor',
        'city' => 'Addis Ababa',
        'project_type' => 'apartments',
        'construction_status' => 'planning',
        'starting_price' => 1000000,
    ]);
    $storeResponse->assertStatus(403);

    $updateResponse = $this->actingAs($this->regularUser)->put(route('developments.update', $this->project), [
        'name' => 'Hacked Project',
        'developer_name' => 'Bad Actor',
        'city' => 'Addis Ababa',
        'project_type' => 'apartments',
        'construction_status' => 'planning',
        'starting_price' => 1000000,
    ]);
    $updateResponse->assertStatus(403);

    $deleteResponse = $this->actingAs($this->regularUser)->delete(route('developments.destroy', $this->project));
    $deleteResponse->assertStatus(403);
});
