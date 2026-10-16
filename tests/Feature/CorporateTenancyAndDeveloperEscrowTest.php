<?php

use App\Models\CompanyWhiteLabel;
use App\Models\ConstructionMilestone;
use App\Models\CorporateEmployee;
use App\Models\CorporateTenant;
use App\Models\DeveloperEscrowDisbursement;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\CorporateTenancySeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Corporate Housing Admin',
        'email' => 'corp.test.admin@ethiopianairlines.com',
        'password' => Hash::make('password123'),
        'role' => 'company',
    ]);

    $this->seed(CorporateTenancySeeder::class);
    $this->tenant = CorporateTenant::first();
});

test('corporate tenant portal renders and displays active workforce allocations and statistics', function () {
    $response = $this->actingAs($this->user)->get(route('corporate.portal'));

    $response->assertOk();
    $response->assertSee('Ethiopian Airlines Group');
    $response->assertSee('Active Unit Allocations');
    $response->assertSee('Eligible Staff Roster');
    $response->assertSee('Bole Sun Tower Penthouse #1201');
    $response->assertSee('Captain Michael Tadesse');
});

test('corporate admin can register eligible employee in housing roster', function () {
    $response = $this->actingAs($this->user)->post(route('corporate.employees.store'), [
        'corporate_tenant_id' => $this->tenant->id,
        'full_name' => 'First Officer Dawit Alemu',
        'employee_id_number' => 'ET-CREW-3301',
        'email' => 'dawit.alemu@ethiopianairlines.com',
        'phone' => '+251 91 999 8888',
        'department' => 'Flight Operations',
        'designation' => 'First Officer',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('corporate_employees', [
        'employee_id_number' => 'ET-CREW-3301',
        'full_name' => 'First Officer Dawit Alemu',
    ]);
});

test('corporate admin can allocate leased residential unit to employee occupant', function () {
    $emp = CorporateEmployee::where('corporate_tenant_id', $this->tenant->id)->first();

    $response = $this->actingAs($this->user)->post(route('corporate.assign-lease'), [
        'corporate_tenant_id' => $this->tenant->id,
        'corporate_employee_id' => $emp->id,
        'unit_identifier' => 'Old Airport Villa Suite #3',
        'assigned_from' => '2026-03-01',
        'assigned_until' => '2026-12-31',
        'monthly_rent_etb' => 60000.00,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('corporate_lease_assignments', [
        'unit_identifier' => 'Old Airport Villa Suite #3',
        'corporate_employee_id' => $emp->id,
        'monthly_rent_etb' => 60000.00,
    ]);
});

test('corporate billing statement generates itemized list with Ethiopian VAT breakdown', function () {
    $response = $this->actingAs($this->user)->get(route('corporate.billing-statement', $this->tenant));

    $response->assertOk();
    $response->assertSee('Consolidated Billing Statement');
    $response->assertSee('Ethiopian Airlines Group');
    $response->assertSee('Ethiopian VAT (15%)');
    $response->assertSee('Total Amount Due');
});

test('developer can request milestone escrow disbursement and admin can approve', function () {
    // Only the project's own developer may request an escrow disbursement, so
    // the project must belong to the developer who is asking.
    $owner = User::create([
        'name' => 'Sunshine Developer Owner',
        'email' => 'sunshine.owner@example.com',
        'password' => Hash::make('password123'),
        'role' => 'developer',
    ]);

    $project = Project::create([
        'project_code' => 'DEV-ESC-2026',
        'name' => 'Kazanchis Heights Development',
        'slug' => 'kazanchis-heights-development',
        'developer_name' => 'Sunshine Construction',
        'developer_user_id' => $owner->id,
        'city' => 'Addis Ababa',
        'subcity' => 'Kirkos',
        'project_type' => 'residential',
        'construction_status' => 'under_construction',
        'status' => 'published',
    ]);

    $milestone = ConstructionMilestone::create([
        'project_id' => $project->id,
        'milestone_name' => 'Foundation Pouring',
        'percentage' => 100,
        'target_date' => '2026-04-01',
        'status' => 'completed',
        'order_index' => 1,
    ]);

    // Request escrow disbursement (the project's own developer only)
    $response = $this->actingAs($owner)->post(route('developer.escrow.request', $project), [
        'milestone_id' => $milestone->id,
        'amount_etb' => 1800000.00,
        'bank_reference' => 'CBE-ESC-998877',
        'inspection_notes' => 'City engineer approved foundation inspection test results.',
    ]);

    $response->assertRedirect();
    $disbursement = DeveloperEscrowDisbursement::where('project_id', $project->id)->first();
    expect($disbursement)->not->toBeNull();
    expect($disbursement->status)->toBe('pending');
    expect((float) $disbursement->amount_etb)->toBe(1800000.00);

    // Admin approves escrow disbursement
    $admin = User::create([
        'name' => 'Finance Inspector Admin',
        'email' => 'inspector.admin@gtmrealestate.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $approveResponse = $this->actingAs($admin)->post(route('developer.escrow.approve', $disbursement));
    $approveResponse->assertRedirect();

    $disbursement->refresh();
    expect($disbursement->status)->toBe('disbursed');
    expect($disbursement->verified_by)->toBe($admin->id);
    expect($disbursement->disbursed_at)->not->toBeNull();
});

test('company white label branding resolves correctly by subdomain and custom domain', function () {
    $whiteLabel = CompanyWhiteLabel::create([
        'company_name' => 'Ayat Real Estate PLC',
        'subdomain' => 'ayatresidences',
        'custom_domain' => 'ayat-homes.et',
        'brand_name' => 'Ayat Smart Residences',
        'primary_color' => '#047857',
        'secondary_color' => '#111827',
        'is_active' => true,
    ]);

    // Test resolution by custom domain
    $resolvedDomain = CompanyWhiteLabel::resolveByHostOrSubdomain('ayat-homes.et');
    expect($resolvedDomain)->not->toBeNull();
    expect($resolvedDomain->id)->toBe($whiteLabel->id);
    expect($resolvedDomain->brand_name)->toBe('Ayat Smart Residences');

    // Test resolution by subdomain
    $resolvedSubdomain = CompanyWhiteLabel::resolveByHostOrSubdomain('ayatresidences.gtmrealestate.com');
    expect($resolvedSubdomain)->not->toBeNull();
    expect($resolvedSubdomain->id)->toBe($whiteLabel->id);
});
