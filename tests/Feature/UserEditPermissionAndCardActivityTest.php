<?php

use App\Models\Agent;
use App\Models\Lease;
use App\Models\Permission;
use App\Models\Property;
use App\Models\PropertyReport;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->adminUser = User::create([
        'name' => 'Super Administrator',
        'email' => 'admin.perm.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->targetUser = User::create([
        'name' => 'Target John Doe',
        'email' => 'target.user.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);
});

test('admin can access dashboard edit user view without 403 error', function () {
    $response = $this->actingAs($this->adminUser)->get(route('dashboard', [
        'edit_user' => $this->targetUser->id,
        'section' => 'users',
    ]));

    $response->assertStatus(200);
    $response->assertSee('Edit User: '.$this->targetUser->name);
    $response->assertDontSee('403 • Permission Denied');
    $response->assertDontSee('Access denied.');
});

test('user with role admin via user_roles relationship can access dashboard edit user without 403 error', function () {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);

    $relAdminUser = User::create([
        'name' => 'Relationship Admin',
        'email' => 'rel.admin.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'staff',
    ]);
    $relAdminUser->roles()->attach($adminRole->id);

    $response = $this->actingAs($relAdminUser)->get(route('dashboard', [
        'edit_user' => $this->targetUser->id,
        'section' => 'users',
    ]));

    $response->assertStatus(200);
    $response->assertSee('Edit User: '.$this->targetUser->name);
    $response->assertDontSee('403 • Permission Denied');
    $response->assertDontSee('Access denied.');
});

test('user granted users.update permission can edit user without 403', function () {
    $updatePerm = Permission::firstOrCreate(['key' => 'users.update'], [
        'label' => 'Update Users',
        'group' => 'users',
    ]);

    $staffUser = User::create([
        'name' => 'Staff Editor',
        'email' => 'staff.editor.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'moderator',
    ]);

    $staffUser->userPermissions()->create([
        'permission_id' => $updatePerm->id,
        'granted' => true,
    ]);

    // 1. Can view dashboard edit user
    $viewResponse = $this->actingAs($staffUser)->get(route('dashboard', [
        'edit_user' => $this->targetUser->id,
        'section' => 'users',
    ]));
    $viewResponse->assertStatus(200);
    $viewResponse->assertDontSee('403 • Permission Denied');

    // 2. Can submit update user form
    $updateResponse = $this->actingAs($staffUser)->put(route('dashboard.users.update', $this->targetUser), [
        'name' => 'Target Updated Name',
        'email' => $this->targetUser->email,
        'role' => 'buyer',
    ]);
    $updateResponse->assertRedirect(route('dashboard', ['section' => 'users']));

    expect($this->targetUser->fresh()->name)->toBe('Target Updated Name');
});

test('user granted users.suspend can approve or suspend user status without 403', function () {
    $suspendPerm = Permission::firstOrCreate(['key' => 'users.suspend'], [
        'label' => 'Suspend Users',
        'group' => 'users',
    ]);

    $staffUser = User::create([
        'name' => 'Staff Supervisor',
        'email' => 'staff.sup.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'moderator',
    ]);

    $staffUser->userPermissions()->create([
        'permission_id' => $suspendPerm->id,
        'granted' => true,
    ]);

    // Suspend
    $suspendResponse = $this->actingAs($staffUser)->post(route('dashboard.users.status', $this->targetUser), [
        'status' => 'suspended',
    ]);
    $suspendResponse->assertSessionHasNoErrors();
    expect($this->targetUser->fresh()->status)->toBe('suspended');

    // Approve / Activate
    $approveResponse = $this->actingAs($staffUser)->post(route('dashboard.users.status', $this->targetUser), [
        'status' => 'active',
    ]);
    $approveResponse->assertSessionHasNoErrors();
    expect($this->targetUser->fresh()->status)->toBe('active');
});

test('user without permissions cannot edit or delete users and gets 403', function () {
    $regularUser = User::create([
        'name' => 'Regular Buyer',
        'email' => 'regular.buyer.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $response = $this->actingAs($regularUser)->get(route('dashboard', [
        'edit_user' => $this->targetUser->id,
        'section' => 'users',
    ]));
    $response->assertStatus(403);

    $updateResponse = $this->actingAs($regularUser)->put(route('dashboard.users.update', $this->targetUser), [
        'name' => 'Hacked Name',
        'email' => $this->targetUser->email,
        'role' => 'buyer',
    ]);
    $updateResponse->assertStatus(403);

    $deleteResponse = $this->actingAs($regularUser)->delete(route('dashboard.users.delete', $this->targetUser));
    $deleteResponse->assertStatus(403);
});

test('company dashboard cards show clean counts without activity sub-labels', function () {
    $companyUser = User::create([
        'name' => 'GTM Real Estate Co',
        'email' => 'company.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'company',
    ]);

    $response = $this->actingAs($companyUser)->get(route('company.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Branches');
    $response->assertSee('Agents');
    $response->assertSee('Listings');
    $response->assertSee('Properties');
    $response->assertSee('Clients');
    $response->assertSee('Transactions');
    $response->assertSee('Client Reports');

    // Confirm the activity sub-labels are removed from the cards
    $response->assertDontSee('5 Active');
    $response->assertDontSee('1 Verified');
    $response->assertDontSee('Active Feed');
    $response->assertDontSee('Auditable');
});

test('overdue rent and client incident alerts are displayed in notification dropdown only and not in dashboard top overview', function () {
    $agent = Agent::create([
        'name' => 'Test Agent Desk',
        'email' => 'test.agent.'.uniqid().'@example.com',
        'phone' => '+251911223344',
        'is_active' => true,
    ]);

    $property = Property::create([
        'property_code' => 'ETH-TEST-'.uniqid(),
        'title' => 'Test Rental Villa',
        'slug' => 'test-rental-villa-'.uniqid(),
        'price' => 50000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 200,
        'type' => 'rent',
        'property_category' => 'house',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole',
        'agent_id' => $agent->id,
    ]);

    $lease = Lease::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'tenant_name' => 'Abebe Tenant',
        'tenant_email' => 'abebe.tenant@example.com',
        'tenant_phone' => '0911001122',
        'start_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
        'end_date' => Carbon::now()->addMonths(10)->format('Y-m-d'),
        'duration_months' => 12,
        'monthly_rent' => 50000.00,
        'security_deposit' => 100000.00,
        'payment_frequency' => 'monthly',
        'status' => 'active',
    ]);
    $lease->generateSchedules();

    // Mark one schedule overdue
    $overdueSchedule = $lease->schedules->first();
    $overdueSchedule->update([
        'due_date' => Carbon::now()->subMonth()->format('Y-m-d'),
        'status' => 'pending',
    ]);

    // Create a pending client report
    PropertyReport::create([
        'user_id' => $this->targetUser->id,
        'property_id' => $property->id,
        'subject' => 'Suspicious listing duplicate',
        'reason' => 'scam',
        'description' => 'Duplicate photos and wrong contact details.',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

    $response->assertStatus(200);

    // 1. Notification dropdown contains the alert items and action links
    $response->assertSee('Admin Alerts &amp; Notifications', false);
    $response->assertSee('Overdue Rent Installment(s) Pending Collection', false);
    $response->assertSee('Review Overdue Leases &rarr;', false);
    $response->assertSee('Client Incident &amp; Misleading Report(s) Awaiting Review', false);
    $response->assertSee('Investigate Reports &rarr;', false);

    // 2. Overview section does NOT render the top banners
    $content = $response->getContent();
    $overviewContent = substr($content, strpos($content, 'data-section="overview"'));
    $overviewContent = substr($overviewContent, 0, strpos($overviewContent, 'Total Properties'));

    expect($overviewContent)->not()->toContain('Overdue Rent Installment(s) Pending Collection');
    expect($overviewContent)->not()->toContain('Client Incident & Misleading Report(s) Awaiting Review');
});
