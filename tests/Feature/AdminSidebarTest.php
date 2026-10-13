<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::create([
        'name' => 'Sidebar Admin',
        'email' => 'sidebar.admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);
});

test('admin sidebar follows the canonical section order', function () {
    $response = $this->actingAs($this->admin)->get(route('dashboard'));

    $response->assertOk();

    $expectedOrder = [
        'Home',
        'Overview',
        'Users',
        'Properties',
        'Orders',
        'Listings',
        'Hosts',
        'Companies',
        'Developers',
        'Projects',
        'Bookings',
        'Transactions',
        'Revenue',
        'Reports',
        'Job Posting',
        'Job Applications',
        'Blogs and News',
        'Lease and Rental',
        'Offers',
        'Message',
        'Verification Requests',
        'Roles and Permissions',
        'Site Settings',
    ];

    $position = 0;
    foreach ($expectedOrder as $sectionLabel) {
        $found = strpos($response->getContent(), '>'.$sectionLabel.'<');

        // "Home" and "Overview" are rendered inside composite strings
        if ($found === false) {
            $found = strpos($response->getContent(), $sectionLabel);
        }

        expect($found)->not()->toBeFalse("Missing sidebar entry: {$sectionLabel}");
        expect($found)->toBeGreaterThanOrEqual($position, "Out of order: {$sectionLabel}");
        $position = $found;
    }
});

test('admin dashboard renders every canonical desk section', function () {
    $response = $this->actingAs($this->admin)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Marketplace Listings');
    $response->assertSee('Hosts');
    $response->assertSee('Companies');
    $response->assertSee('Developer accounts running projects');
    $response->assertSee('Developer projects with buildings');
    $response->assertSee('Transaction Engine');
    $response->assertSee('Platform-derived commission indicators');
});

test('role dropdown offers the platform actors without legacy duplicates or guest', function () {
    $response = $this->actingAs($this->admin)->get('/dashboard?section=users');

    $response->assertOk();

    foreach ([
        'admin', 'company', 'finance_officer', 'agent', 'receptionist',
        'buyer', 'tenant', 'traveler', 'host',
        'property_owner', 'landlord', 'developer', 'property_manager',
        'service_provider', 'moderator', 'verification_officer', 'customer_support',
    ] as $role) {
        $response->assertSee('<option value="'.$role.'"', false);
    }

    $response->assertDontSee('<option value="guest"', false);
    $response->assertDontSee('<option value="user"', false);
    $response->assertDontSee('<option value="client"', false);
    $response->assertDontSee('<option value="company_admin"', false);
    $response->assertDontSee('<option value="real_estate_company"', false);
    $response->assertDontSee('<option value="campaign_officer"', false);
});
