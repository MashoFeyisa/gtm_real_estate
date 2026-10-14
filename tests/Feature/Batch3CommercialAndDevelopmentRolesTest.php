<?php

use App\Models\Agent;
use App\Models\AgentClient;
use App\Models\Branch;
use App\Models\CompanyWhiteLabel;
use App\Models\ConstructionMilestone;
use App\Models\DeveloperPaymentPlan;
use App\Models\Inquiry;
use App\Models\Permission;
use App\Models\ProfessionalServiceRequest;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectUnit;
use App\Models\Property;
use App\Models\UnitReservation;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');
});

// ══════════════════════════════════════════════════════════════════════════════
// 1. AGENT ROLE TESTS
// ══════════════════════════════════════════════════════════════════════════════

test('agent can manage direct CRM clients: create, update stage, and delete', function () {
    $agentUser = User::factory()->create(['role' => 'agent']);
    $agent = Agent::create([
        'user_id' => $agentUser->id,
        'name' => 'Addis Premier Broker',
        'email' => 'broker.addis@example.com',
        'phone' => '+251911000111',
        'is_active' => true,
    ]);

    // 1. Create direct client
    $createResponse = $this->actingAs($agentUser)->post(route('agent.clients.store'), [
        'name' => 'Kassahun Worku',
        'email' => 'kassahun@example.com',
        'phone' => '+251911223344',
        'lead_source' => 'referral',
        'stage' => 'qualified',
        'budget' => 25000000,
        'preferred_type' => 'villa',
        'preferred_location' => 'Bole, CMC',
        'notes' => 'Looking for immediate purchase.',
    ]);
    $createResponse->assertRedirect();

    $client = AgentClient::where('email', 'kassahun@example.com')->first();
    expect($client)->not->toBeNull();
    expect($client->agent_id)->toBe($agent->id);
    expect($client->stage)->toBe('qualified');
    expect((float) $client->budget)->toBe(25000000.0);

    // 2. Advance stage
    $stageResponse = $this->actingAs($agentUser)->post(route('agent.clients.stage', $client), [
        'stage' => 'offer',
    ]);
    $stageResponse->assertRedirect();
    expect($client->fresh()->stage)->toBe('offer');

    // 3. Delete client
    $deleteResponse = $this->actingAs($agentUser)->delete(route('agent.clients.destroy', $client));
    $deleteResponse->assertRedirect();
    expect(AgentClient::find($client->id))->toBeNull();
});

test('agent cannot manage clients belonging to another agent', function () {
    $agentUser1 = User::factory()->create(['role' => 'agent']);
    $agent1 = Agent::create([
        'user_id' => $agentUser1->id,
        'name' => 'Agent One',
        'email' => 'agent1@example.com',
    ]);

    $agentUser2 = User::factory()->create(['role' => 'agent']);
    $agent2 = Agent::create([
        'user_id' => $agentUser2->id,
        'name' => 'Agent Two',
        'email' => 'agent2@example.com',
    ]);

    $client = AgentClient::create([
        'agent_id' => $agent1->id,
        'name' => 'Private Client',
        'email' => 'private@example.com',
        'stage' => 'new_lead',
    ]);

    // Agent 2 attempts to change Agent 1 client's stage
    $response = $this->actingAs($agentUser2)->post(route('agent.clients.stage', $client), [
        'stage' => 'closed',
    ]);
    $response->assertForbidden();
    expect($client->fresh()->stage)->toBe('new_lead');

    // Agent 2 attempts to delete Agent 1's client
    $this->actingAs($agentUser2)->delete(route('agent.clients.destroy', $client))->assertForbidden();
    expect(AgentClient::find($client->id))->not->toBeNull();
});

test('admin cannot access individual agent portal due to admin boundary', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('agent.portal'));
    $response->assertForbidden();
});

test('agent can reply to inquiry and advance lead stage', function () {
    $agentUser = User::factory()->create(['role' => 'agent']);
    $agent = Agent::create([
        'user_id' => $agentUser->id,
        'name' => 'Agent Reply Tester',
        'email' => 'reply.agent@example.com',
    ]);

    $inquiry = Inquiry::create([
        'name' => 'Inquiring Buyer',
        'email' => 'inquiry.buyer@example.com',
        'phone' => '+251922334455',
        'subject' => 'Is this property available?',
        'message' => 'Please confirm if inspection is possible this Saturday.',
        'status' => 'new',
        'agent_id' => $agent->id,
    ]);

    $response = $this->actingAs($agentUser)->post(route('agent.inquiries.reply', $inquiry), [
        'reply_message' => 'Yes, inspection is confirmed for Saturday at 10 AM.',
        'status' => 'viewing',
    ]);
    $response->assertRedirect();

    $inquiry->refresh();
    expect($inquiry->status)->toBe('viewing');
    expect($inquiry->read_at)->not->toBeNull();
    expect($inquiry->message)->toContain('Yes, inspection is confirmed for Saturday at 10 AM.');
});

// ══════════════════════════════════════════════════════════════════════════════
// 2. REAL ESTATE COMPANY ROLE TESTS
// ══════════════════════════════════════════════════════════════════════════════

test('real estate company can invite, update, toggle, and delete corporate agents', function () {
    $companyUser = User::factory()->create(['role' => 'company']);
    $branch = Branch::create([
        'name' => 'Bole Commercial Branch',
        'code' => 'BOLE-COM-01',
        'city' => 'Addis Ababa',
        'status' => 'active',
    ]);

    // 1. Create/invite corporate agent
    $storeResponse = $this->actingAs($companyUser)->post(route('company.agents.store'), [
        'name' => 'Abebe Corporate Broker',
        'email' => 'abebe.corporate@example.com',
        'phone' => '+251911556677',
        'branch_id' => $branch->id,
        'commission_rate' => 65.0,
        'license_number' => 'ETH-LIC-7701',
    ]);
    $storeResponse->assertRedirect(route('company.dashboard', ['section' => 'agents']));

    $createdAgent = Agent::where('email', 'abebe.corporate@example.com')->first();
    expect($createdAgent)->not->toBeNull();
    expect($createdAgent->branch_id)->toBe($branch->id);
    expect((float) $createdAgent->commission_rate)->toBe(65.0);
    expect($createdAgent->is_active)->toBeTrue();
    $this->assertDatabaseHas('audit_logs', ['action' => 'agent.created', 'entity_type' => Agent::class, 'entity_id' => $createdAgent->id]);

    // 2. Update agent details & split rate
    $updateResponse = $this->actingAs($companyUser)->put(route('company.agents.update', $createdAgent), [
        'name' => 'Abebe Senior Broker',
        'email' => 'abebe.corporate@example.com',
        'phone' => '+251911556677',
        'branch_id' => $branch->id,
        'commission_rate' => 75.0,
        'license_number' => 'ETH-LIC-7701-SR',
    ]);
    $updateResponse->assertRedirect();
    expect($createdAgent->fresh()->name)->toBe('Abebe Senior Broker');
    expect((float) $createdAgent->fresh()->commission_rate)->toBe(75.0);
    $this->assertDatabaseHas('audit_logs', ['action' => 'agent.updated', 'entity_type' => Agent::class, 'entity_id' => $createdAgent->id]);

    // 3. Toggle active status
    $toggleResponse = $this->actingAs($companyUser)->post(route('company.agents.toggleActive', $createdAgent));
    $toggleResponse->assertRedirect();
    expect($createdAgent->fresh()->is_active)->toBeFalse();
    $this->assertDatabaseHas('audit_logs', ['action' => 'agent.toggled', 'entity_type' => Agent::class, 'entity_id' => $createdAgent->id]);

    // 4. Delete agent
    $deleteResponse = $this->actingAs($companyUser)->delete(route('company.agents.destroy', $createdAgent));
    $deleteResponse->assertRedirect();
    expect(Agent::find($createdAgent->id))->toBeNull();
    $this->assertDatabaseHas('audit_logs', ['action' => 'agent.deleted', 'entity_type' => Agent::class, 'entity_id' => $createdAgent->id]);
});

test('real estate company can merge duplicate listings with properties.merge permission', function () {
    $companyUser = User::factory()->create(['role' => 'company']);

    $primary = Property::create([
        'title' => 'Master Penthouse Suite Bole',
        'slug' => 'master-penthouse-suite-bole',
        'price' => 30000000,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
    ]);

    $duplicate = Property::create([
        'title' => 'Duplicate Penthouse Entry Bole',
        'slug' => 'duplicate-penthouse-entry-bole',
        'price' => 29500000,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
    ]);

    $inquiry = Inquiry::create([
        'name' => 'Interested Buyer',
        'email' => 'buyer.penthouse@example.com',
        'subject' => 'Penthouse Inquiry',
        'message' => 'Still available?',
        'property_id' => $duplicate->id,
    ]);

    $mergeResponse = $this->actingAs($companyUser)->post(route('company.listings.merge'), [
        'primary_property_id' => $primary->id,
        'duplicate_property_id' => $duplicate->id,
    ]);

    $mergeResponse->assertRedirect(route('company.dashboard', ['section' => 'listings']));

    // Inquiries reassigned to master property
    expect($inquiry->fresh()->property_id)->toBe($primary->id);

    // Duplicate property archived and deactivated
    $duplicate->refresh();
    expect($duplicate->is_active)->toBeFalse();
    expect($duplicate->status)->toBe('archived');
    expect($duplicate->title)->toContain('[Merged into #'.$primary->id.']');
});

test('company user without properties.merge permission cannot merge listings', function () {
    $companyUser = User::factory()->create(['role' => 'company']);

    $perm = Permission::where('key', 'properties.merge')->first();
    if ($perm) {
        UserPermissionOverride::create([
            'user_id' => $companyUser->id,
            'permission_id' => $perm->id,
            'granted' => false,
        ]);
    }

    $prop1 = Property::factory()->create();
    $prop2 = Property::factory()->create();

    $response = $this->actingAs($companyUser->fresh())->post(route('company.listings.merge'), [
        'primary_property_id' => $prop1->id,
        'duplicate_property_id' => $prop2->id,
    ]);

    $response->assertForbidden();
});

test('company can reassign client and update white label subscription tier', function () {
    $companyUser = User::factory()->create(['role' => 'company']);
    $client = User::factory()->create(['role' => 'buyer']);

    $agent = Agent::create([
        'user_id' => User::factory()->create(['role' => 'agent'])->id,
        'name' => 'Target Reassigned Agent',
        'email' => 'reassigned@example.com',
    ]);

    $inquiry = Inquiry::create([
        'email' => $client->email,
        'user_id' => $client->id,
        'name' => $client->name,
        'subject' => 'Client Lead Inquiry',
        'message' => 'Needs guidance',
    ]);

    // 1. Reassign client
    $reassignResponse = $this->actingAs($companyUser)->post(route('company.clients.reassign', $client), [
        'agent_id' => $agent->id,
    ]);
    $reassignResponse->assertRedirect();
    expect($inquiry->fresh()->agent_id)->toBe($agent->id);

    // 2. Update subscription tier
    $subResponse = $this->actingAs($companyUser)->post(route('company.subscription.update'), [
        'subscription_tier' => 'enterprise',
    ]);
    $subResponse->assertRedirect();

    $whiteLabel = CompanyWhiteLabel::where('user_id', $companyUser->id)->first();
    expect($whiteLabel)->not->toBeNull();
    expect($whiteLabel->subscription_tier)->toBe('enterprise');
    expect($whiteLabel->subscription_expires_at)->not->toBeNull();
});

test('admin cannot access company dashboard due to admin boundary', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('company.dashboard'));
    $response->assertForbidden();
});

// ══════════════════════════════════════════════════════════════════════════════
// 3. DEVELOPER ROLE TESTS
// ══════════════════════════════════════════════════════════════════════════════

test('developer can create buildings, bulk-generate units, update milestones and progress photos', function () {
    $devUser = User::create([
        'name' => 'Noah Developer',
        'email' => 'noah.dev@example.com',
        'password' => Hash::make('password123'),
        'role' => 'developer',
    ]);

    $project = Project::create([
        'project_code' => 'ETH-DEV-2026-BOLE',
        'name' => 'Noah Sky Residences',
        'slug' => 'noah-sky-residences',
        'developer_name' => 'Noah Construction',
        'developer_user_id' => $devUser->id,
        'city' => 'Addis Ababa',
        'project_type' => 'apartments',
        'construction_status' => 'under_construction',
        'overall_completion_percentage' => 30,
        'starting_price' => 15000000,
        'currency' => 'ETB',
    ]);

    // 1. Create building
    $bldResponse = $this->actingAs($devUser)->post(route('developer.buildings.store', $project), [
        'name' => 'Block C - Sunrise',
        'total_floors' => 'G+10',
        'description' => 'Luxury residential building',
    ]);
    $bldResponse->assertRedirect();

    $building = ProjectBuilding::where('project_id', $project->id)->where('name', 'Block C - Sunrise')->first();
    expect($building)->not->toBeNull();

    // 2. Bulk generate units across 3 floors (3 units per floor = 9 units)
    $genResponse = $this->actingAs($devUser)->post(route('developer.buildings.generate-units', $building), [
        'floor_start' => 1,
        'floor_end' => 3,
        'units_per_floor' => 3,
        'unit_type' => '3_bedroom',
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area_sqm' => 140.0,
        'base_price' => 12000000,
        'price_per_floor_increment' => 200000,
    ]);
    $genResponse->assertRedirect();

    expect(ProjectUnit::where('building_id', $building->id)->count())->toBe(9);
    $topFloorUnit = ProjectUnit::where('building_id', $building->id)->where('floor_number', '3')->first();
    expect((float) $topFloorUnit->price)->toBe(12400000.0); // 12M + (2 * 200k)

    // 3. Create milestone with photo
    $photo = UploadedFile::fake()->image('milestone_progress.jpg');
    $milestoneResponse = $this->actingAs($devUser)->post(route('developer.milestones.store', $project), [
        'milestone_name' => 'Roof Slab Concrete',
        'percentage' => 80,
        'target_date' => 'Q4 2026',
        'status' => 'in_progress',
        'photo' => $photo,
    ]);
    $milestoneResponse->assertRedirect();

    $milestone = ConstructionMilestone::where('project_id', $project->id)->where('milestone_name', 'Roof Slab Concrete')->first();
    expect($milestone)->not->toBeNull();
    expect($milestone->percentage)->toBe(80);
    expect($milestone->photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($milestone->photo_path);

    // 4. Update milestone percentage
    $this->actingAs($devUser)->put(route('developer.milestones.update', [$project, $milestone]), [
        'milestone_name' => 'Roof Slab Concrete',
        'percentage' => 100,
        'status' => 'completed',
    ])->assertRedirect();

    expect($milestone->fresh()->percentage)->toBe(100);
    expect($project->fresh()->overall_completion_percentage)->toBe(100);
});

test('developer can manage payment plans and transition reservation status to contract signed', function () {
    $devUser = User::create([
        'name' => 'Flintstone Developer',
        'email' => 'flintstone.dev@example.com',
        'password' => Hash::make('password123'),
        'role' => 'developer',
    ]);

    $project = Project::create([
        'project_code' => 'ETH-DEV-2026-CMC',
        'name' => 'CMC Prime Park',
        'slug' => 'cmc-prime-park',
        'developer_name' => 'Flintstone Engineering',
        'developer_user_id' => $devUser->id,
        'city' => 'Addis Ababa',
        'starting_price' => 11000000,
        'currency' => 'ETB',
    ]);

    $unit = ProjectUnit::create([
        'project_id' => $project->id,
        'unit_number' => 'P-101',
        'floor_number' => '1',
        'unit_type' => '2_bedroom',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area_sqm' => 95.0,
        'price' => 11000000,
        'currency' => 'ETB',
        'status' => 'available',
    ]);

    // 1. Create payment plan installment
    $planResponse = $this->actingAs($devUser)->post(route('developer.payment-plans.store', $project), [
        'installment_name' => 'Contract Execution Deposit',
        'percentage' => 25.0,
        'milestone_trigger' => 'Signing Notarized Contract',
        'order_index' => 1,
    ]);
    $planResponse->assertRedirect();

    $plan = DeveloperPaymentPlan::where('project_id', $project->id)->first();
    expect($plan)->not->toBeNull();

    // 2. Reservation transition to contract_signed
    $reservation = UnitReservation::create([
        'reservation_reference' => 'RES-DEV-0099',
        'project_id' => $project->id,
        'unit_id' => $unit->id,
        'buyer_name' => 'Marta Girma',
        'buyer_email' => 'marta@example.com',
        'buyer_phone' => '+251911223344',
        'status' => 'pending',
    ]);

    $resResponse = $this->actingAs($devUser)->put(route('developer.reservations.status', $reservation), [
        'status' => 'contract_signed',
        'deposit_amount' => 2750000,
        'contract_date' => now()->toDateString(),
        'payment_plan_id' => $plan->id,
    ]);
    $resResponse->assertRedirect();

    $reservation->refresh();
    expect($reservation->status)->toBe('contract_signed');
    expect((float) $reservation->deposit_amount)->toBe(2750000.0);
    expect($unit->fresh()->status)->toBe('contracted');
});

// ══════════════════════════════════════════════════════════════════════════════
// 4. PROFESSIONAL SERVICE PROVIDER ROLE TESTS
// ══════════════════════════════════════════════════════════════════════════════

test('professional service provider can configure profile and rates', function () {
    $proUser = User::create([
        'name' => 'Eng. Solomon Tadesse',
        'email' => 'solomon.engineer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'professional',
    ]);

    $response = $this->actingAs($proUser)->post(route('professional.profile.update'), [
        'profession' => 'Structural Engineer & Valuer',
        'hourly_rate' => 3500.00,
        'license_number' => 'ENG-VAL-ETH-2026',
        'bio' => 'Certified structural inspector with over 15 years experience in Addis Ababa high-rises.',
        'portfolio_summary' => 'Inspected over 40 major commercial complexes in Bole and Kirkos subcities.',
    ]);

    $response->assertRedirect();
    $proUser->refresh();

    expect($proUser->profession)->toBe('Structural Engineer & Valuer');
    expect((float) $proUser->hourly_rate)->toBe(3500.00);
    expect($proUser->license_number)->toBe('ENG-VAL-ETH-2026');
    expect($proUser->bio)->toContain('Certified structural inspector');
});

test('professional can submit fee quotation and schedule appointment on service request', function () {
    $proUser = User::create([
        'name' => 'Arc. Bethlehem',
        'email' => 'bethlehem.arch@example.com',
        'password' => Hash::make('password123'),
        'role' => 'service_provider',
    ]);

    $client = User::factory()->create(['role' => 'buyer']);

    $request = ProfessionalServiceRequest::create([
        'request_code' => 'REQ-VAL-'.uniqid(),
        'title' => 'Villa Mortgage Valuation',
        'service_type' => 'property_valuation',
        'professional_user_id' => $proUser->id,
        'user_id' => $client->id,
        'description' => 'Full valuation appraisal of G+2 residential villa for bank mortgage.',
        'status' => 'requested',
    ]);

    // 1. Submit quotation
    $quoteResponse = $this->actingAs($proUser)->post(route('professional.requests.quote', $request), [
        'quote_amount' => 45000.00,
        'estimated_days' => 4,
        'quote_notes' => 'Includes comprehensive on-site engineering inspection and signed legal appraisal report.',
    ]);
    $quoteResponse->assertRedirect();

    $request->refresh();
    expect((float) $request->quote_amount)->toBe(45000.00);
    expect($request->estimated_days)->toBe(4);
    expect($request->quote_notes)->toContain('comprehensive on-site engineering inspection');

    // 2. Schedule appointment
    $appointmentResponse = $this->actingAs($proUser)->post(route('professional.requests.appointment', $request), [
        'scheduled_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'notes' => 'On-site measurement inspection at property address.',
    ]);
    $appointmentResponse->assertRedirect();

    $request->refresh();
    expect($request->scheduled_at)->not->toBeNull();
    expect($request->status)->toBe('in_progress');
});

test('professional service provider cross-isolation: other provider cannot quote foreign request', function () {
    $pro1 = User::create([
        'name' => 'Provider One',
        'email' => 'provider1@example.com',
        'password' => Hash::make('password123'),
        'role' => 'professional',
    ]);

    $pro2 = User::create([
        'name' => 'Provider Two',
        'email' => 'provider2@example.com',
        'password' => Hash::make('password123'),
        'role' => 'professional',
    ]);

    $request = ProfessionalServiceRequest::create([
        'request_code' => 'REQ-LEG-'.uniqid(),
        'title' => 'Title Deed Verification',
        'service_type' => 'legal_consultation',
        'professional_user_id' => $pro1->id,
        'user_id' => $pro1->id,
        'description' => 'Title deed verification',
        'status' => 'requested',
    ]);

    // Provider 2 tries to quote on Provider 1's request
    $response = $this->actingAs($pro2)->post(route('professional.requests.quote', $request), [
        'quote_amount' => 20000,
        'estimated_days' => 2,
    ]);
    $response->assertForbidden();

    expect((float) $request->fresh()->quote_amount)->toBe(0.0);
    expect($request->fresh()->status)->toBe('requested');
    expect($request->fresh()->quote_notes)->toBeNull();
});

test('admin cannot open individual professional dashboard due to admin boundary', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('professional.dashboard'));
    $response->assertForbidden();
});
