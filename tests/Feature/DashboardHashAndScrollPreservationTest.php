<?php

use App\Models\Agent;
use App\Models\Booking;
use App\Models\Permission;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\PropertyTaxonomySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PropertyTaxonomySeeder::class);

    $this->admin = User::create([
        'name' => 'Dashboard Preservation Admin',
        'email' => 'preservation.admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);
});

test('dashboard view contains script handling preventDefault, history replaceState, and sessionStorage persistence', function () {
    $response = $this->actingAs($this->admin)->get(route('dashboard'));
    $response->assertOk();

    // 1. preventDefault and replaceState for hash switching
    $response->assertSee('history.replaceState', false);
    $response->assertSee('event.preventDefault()', false);
    $response->assertSee('dashboard_active_section', false);

    // 2. Sidebar scroll persistence in sessionStorage
    $response->assertSee('gtm_sidebar_scroll_', false);
    $response->assertSee('sessionStorage.getItem', false);
    $response->assertSee('sessionStorage.setItem', false);

    // 3. Main content scroll position preservation
    $response->assertSee('gtm_main_scroll_', false);
    $response->assertSee('showDashboardToast', false);
});

test('site settings update redirects to dashboard#settings or returns JSON on AJAX', function () {
    // Normal form submit redirects to #settings
    $postData = [
        'site_name' => 'GTM Real Estate Updated',
        'contact_email' => 'support@gtm.et',
        'contact_phone' => '+251911002233',
        'contact_address' => 'Bole Medhanealem, Addis Ababa, Ethiopia',
    ];

    $response = $this->actingAs($this->admin)->post(route('dashboard.settings.update'), $postData);
    $response->assertRedirect(route('dashboard', ['section' => 'settings']));

    // AJAX form submit returns JSON with section settings
    $ajaxResponse = $this->actingAs($this->admin)->postJson(route('dashboard.settings.update'), $postData);
    $ajaxResponse->assertOk()
        ->assertJson([
            'success' => true,
            'section' => 'settings',
        ]);
});

test('user management store and update redirect to dashboard#users or return JSON on AJAX', function () {
    $userData = [
        'name' => 'Abebe Bikila',
        'email' => 'abebe.test@example.com',
        'phone' => '+251911223344',
        'role' => 'agent',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
    ];

    // Create user via standard form submission
    $response = $this->actingAs($this->admin)->post(route('dashboard.users.store'), $userData);
    $response->assertRedirect(route('dashboard', ['section' => 'users']));

    $createdUser = User::where('email', 'abebe.test@example.com')->firstOrFail();

    // Update user via AJAX submission
    $updateData = [
        'name' => 'Abebe Bikila Updated',
        'email' => 'abebe.test@example.com',
        'phone' => '+251911223344',
        'role' => 'agent',
    ];

    $ajaxResponse = $this->actingAs($this->admin)->putJson(route('dashboard.users.update', $createdUser), $updateData);
    $ajaxResponse->assertOk()
        ->assertJson([
            'success' => true,
            'section' => 'users',
        ]);
});

test('property save redirects to dashboard#properties or returns JSON on AJAX', function () {
    $propertyData = [
        'title' => 'Test Modern Villa Bole',
        'type' => 'sale',
        'price' => 15000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 320,
        'status' => 'published',
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanealem',
        'property_category' => 'villa',
        'description' => 'Beautiful luxury property in Bole.',
    ];

    // Standard store redirects to dashboard
    $response = $this->actingAs($this->admin)->post(route('dashboard.properties.store'), $propertyData);
    $response->assertRedirect(route('dashboard'));

    $property = Property::where('title', 'Test Modern Villa Bole')->firstOrFail();

    // AJAX update returns JSON
    $updateData = array_merge($propertyData, [
        'title' => 'Test Modern Villa Bole Updated',
    ]);
    $ajaxResponse = $this->actingAs($this->admin)->putJson(route('dashboard.properties.update', $property), $updateData);
    $ajaxResponse->assertOk()
        ->assertJson([
            'success' => true,
            'section' => 'properties',
        ]);
});

test('company listings actions redirect to dashboard#listings or return JSON on AJAX', function () {
    $companyUser = User::create([
        'name' => 'Company Manager User',
        'email' => 'company.manager@example.com',
        'password' => Hash::make('password123'),
        'role' => 'company',
    ]);

    $property = Property::create([
        'title' => 'Listing Test Property',
        'slug' => 'listing-test-prop',
        'price' => 5000000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 90,
        'type' => 'sale',
        'property_category' => 'apartment',
        'city' => 'Addis Ababa',
        'address' => 'Kazanchis',
        'status' => 'draft',
        'is_active' => true,
    ]);

    // Standard publish redirects to #listings
    $response = $this->actingAs($companyUser)->post(route('company.listings.publish', $property));
    $response->assertRedirect(route('dashboard').'#listings');

    // AJAX status returns JSON
    $ajaxResponse = $this->actingAs($companyUser)->postJson(route('company.listings.status', ['property' => $property, 'status' => 'published']));
    $ajaxResponse->assertOk()
        ->assertJson([
            'success' => true,
            'section' => 'listings',
        ]);
});

test('stay booking status update redirects to dashboard#stay-bookings or returns JSON on AJAX', function () {
    $property = Property::create([
        'title' => 'Stay Guest House',
        'slug' => 'stay-guest-house-scroll',
        'type' => 'stay',
        'property_category' => 'guest_house',
        'price' => 3000,
        'city' => 'Addis Ababa',
        'address' => 'Bole Atlas',
        'is_active' => true,
    ]);

    $booking = Booking::create([
        'property_id' => $property->id,
        'user_id' => $this->admin->id,
        'guest_name' => 'Abebe Guest',
        'guest_email' => 'guest@example.com',
        'guest_phone' => '+251911000999',
        'check_in_date' => now()->addDays(2)->toDateString(),
        'check_out_date' => now()->addDays(5)->toDateString(),
        'total_nights' => 3,
        'guests_count' => 2,
        'nightly_price' => 3000,
        'accommodation_total' => 9000,
        'total_amount' => 9000,
        'status' => 'pending',
    ]);

    // Standard status update redirects to #stay-bookings
    $response = $this->actingAs($this->admin)->put(route('bookings.update-status', $booking), [
        'status' => 'confirmed',
    ]);
    $response->assertRedirect(route('dashboard').'#stay-bookings');

    // AJAX status update returns JSON
    $ajaxResponse = $this->actingAs($this->admin)->putJson(route('bookings.update-status', $booking), [
        'status' => 'cancelled',
    ]);
    $ajaxResponse->assertOk()
        ->assertJson([
            'success' => true,
            'section' => 'stay-bookings',
        ]);
});

test('roles and permissions updates redirect to dashboard#roles-permissions or return JSON on AJAX', function () {
    $permissions = Permission::query()->where('group', 'moderator')->take(2)->get();

    // Standard form submit redirects to #roles-permissions
    $response = $this->actingAs($this->admin)->post(route('dashboard.roles.permissions.update'), [
        'persona' => 'moderator',
        'permission_ids' => $permissions->pluck('id')->all(),
    ]);
    $response->assertRedirect(route('dashboard').'#roles-permissions');

    // AJAX form submit returns JSON
    $ajaxResponse = $this->actingAs($this->admin)->postJson(route('dashboard.roles.permissions.update'), [
        'persona' => 'moderator',
        'permission_ids' => [$permissions->first()->id],
    ]);
    $ajaxResponse->assertOk()
        ->assertJson([
            'success' => true,
            'section' => 'roles-permissions',
        ]);
});

test('verification requests updates redirect to dashboard#verifications or return JSON on AJAX', function () {
    $officer = User::create([
        'name' => 'Officer Almaz',
        'email' => 'officer.preservation@example.com',
        'password' => Hash::make('password123'),
        'role' => 'verification_officer',
    ]);

    $company = Agent::create([
        'name' => 'Verification Agency Inc',
        'email' => 'agency.verify@example.com',
        'phone' => '+251911445566',
        'license_verified' => false,
    ]);

    // Standard decision redirects to #verifications
    $response = $this->actingAs($officer)->post(route('verification.companies.decision', $company), [
        'decision' => 'verify',
        'notes' => 'All documents verified successfully.',
    ]);
    $response->assertRedirect(route('dashboard').'#verifications');

    // AJAX decision returns JSON
    $ajaxResponse = $this->actingAs($officer)->postJson(route('verification.companies.decision', $company), [
        'decision' => 'revoke',
        'notes' => 'Missing updated trade license.',
    ]);
    $ajaxResponse->assertOk()
        ->assertJson([
            'success' => true,
            'section' => 'verifications',
        ]);
});
