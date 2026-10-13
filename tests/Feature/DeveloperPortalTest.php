<?php

use App\Models\ConstructionMilestone;
use App\Models\DeveloperPaymentPlan;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectUnit;
use App\Models\UnitReservation;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->developer = User::create([
        'name' => 'Developer Dawit',
        'email' => 'dawit.developer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'developer',
    ]);

    $this->project = Project::create([
        'project_code' => 'ETH-DEV-AA-2026-BOLE',
        'name' => 'Bole Horizon Towers',
        'slug' => 'bole-horizon-towers',
        'developer_name' => 'Dawit Constructions',
        'city' => 'Addis Ababa',
        'subcity' => 'Bole',
        'project_type' => 'mixed_use',
        'construction_status' => 'under_construction',
        'overall_completion_percentage' => 62,
        'starting_price' => 12500000.00,
        'currency' => 'ETB',
        'total_buildings_count' => 2,
        'total_units_count' => 40,
    ]);

    $this->building = ProjectBuilding::create([
        'project_id' => $this->project->id,
        'name' => 'Tower A',
        'total_floors' => 'G+14',
        'units_count' => 20,
        'description' => 'Residential tower with 2 and 3 bedroom units.',
    ]);

    $this->unit = ProjectUnit::create([
        'project_id' => $this->project->id,
        'building_id' => $this->building->id,
        'unit_number' => 'A-1201',
        'floor_number' => '12',
        'unit_type' => '3_bedroom',
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area_sqm' => 145.50,
        'price' => 13750000.00,
        'currency' => 'ETB',
        'status' => 'available',
    ]);

    $this->milestone = ConstructionMilestone::create([
        'project_id' => $this->project->id,
        'milestone_name' => 'Foundation & Structure',
        'percentage' => 75,
        'target_date' => 'Q2 2027',
        'status' => 'in_progress',
        'order_index' => 1,
    ]);

    $this->plan = DeveloperPaymentPlan::create([
        'project_id' => $this->project->id,
        'installment_name' => 'Reservation Deposit',
        'percentage' => 10.00,
        'milestone_trigger' => 'On Reservation',
        'order_index' => 1,
    ]);

    $this->reservation = UnitReservation::create([
        'reservation_reference' => 'RES-BOLE-0001',
        'project_id' => $this->project->id,
        'unit_id' => $this->unit->id,
        'buyer_name' => 'Selamawit Bekele',
        'buyer_email' => 'selam@example.com',
        'buyer_phone' => '+251 91 111 2233',
        'status' => 'pending',
    ]);
});

test('developer lands on the developer portal after login', function () {
    $response = $this->post(route('login'), [
        'email' => $this->developer->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('developer.portal'));

    expect($this->developer->dashboard_url)->toBe(route('developer.portal'));
});

test('developer portal displays every section of the developer role', function () {
    $response = $this->actingAs($this->developer)->get(route('developer.portal'));

    $response->assertOk();
    $response->assertSee('Developer Portal');
    $response->assertSee('Projects');
    $response->assertSee('Buildings');
    $response->assertSee('Units');
    $response->assertSee('Construction Progress');
    $response->assertSee('Pricing');
    $response->assertSee('Payment Plans');
    $response->assertSee('Buyers');
    $response->assertSee('Reservations');
});

test('developer portal lists projects buildings units milestones plans and reservations', function () {
    $response = $this->actingAs($this->developer)->get(route('developer.portal'));

    $response->assertOk();
    $response->assertSee('Bole Horizon Towers');
    $response->assertSee('Tower A');
    $response->assertSee('A-1201');
    $response->assertSee('Foundation & Structure');
    $response->assertSee('Reservation Deposit');
    $response->assertSee('Selamawit Bekele');
    $response->assertSee('RES-BOLE-0001');
});

test('developer portal keeps the role in the signed-in sidebar instead of a capabilities panel', function () {
    $response = $this->actingAs($this->developer)->get(route('developer.portal'));

    $response->assertOk();
    $response->assertSee('Signed in');
    $response->assertSee('Developer');
    $response->assertDontSee('Your Capabilities');
    $response->assertDontSee('You are signed in as');
});

test('revoking a permission hides its section from the developer portal', function () {
    UserPermissionOverride::create([
        'user_id' => $this->developer->id,
        'permission_id' => Permission::query()->where('key', 'payment_plans.manage')->value('id'),
        'granted' => false,
    ]);

    $response = $this->actingAs($this->developer->fresh())->get(route('developer.portal'));

    $response->assertOk();
    $response->assertDontSee('Payment Plans');
    $response->assertDontSee('Reservation Deposit');
    $response->assertSee('Construction Progress');
});

test('non developers cannot access the developer portal', function () {
    $buyer = User::create([
        'name' => 'Buyer Bezawit',
        'email' => 'bezawit.buyer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->actingAs($buyer)->get(route('developer.portal'))->assertForbidden();
});

test('admins can access the developer portal', function () {
    $admin = User::create([
        'name' => 'Platform Admin',
        'email' => 'admin.dev@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)->get(route('developer.portal'))->assertOk();
});

test('developer can create a project when granted projects manage', function () {
    $this->actingAs($this->developer)->post(route('developments.store'), [
        'name' => 'Ayat Modern Estates',
        'developer_name' => 'Dawit Constructions',
        'city' => 'Addis Ababa',
        'subcity' => 'Ayat',
        'project_type' => 'residential',
        'construction_status' => 'planning',
        'starting_price' => 8500000,
    ])->assertRedirect();

    expect(Project::query()->where('name', 'Ayat Modern Estates')->exists())->toBeTrue();
});

test('developer without projects manage cannot create a project', function () {
    UserPermissionOverride::create([
        'user_id' => $this->developer->id,
        'permission_id' => Permission::query()->where('key', 'projects.create')->value('id'),
        'granted' => false,
    ]);

    $this->actingAs($this->developer->fresh())->post(route('developments.store'), [
        'name' => 'Ayat Modern Estates',
        'developer_name' => 'Dawit Constructions',
        'city' => 'Addis Ababa',
        'project_type' => 'residential',
        'construction_status' => 'planning',
        'starting_price' => 8500000,
    ])->assertForbidden();

    expect(Project::query()->where('name', 'Ayat Modern Estates')->exists())->toBeFalse();
});
