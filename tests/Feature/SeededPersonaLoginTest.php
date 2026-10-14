<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

/**
 * One seeded demo account per role (see the README table). Running the seeder
 * must never create a second account for a role, and every persona must be able
 * to sign in and open its own desk.
 */
test('database seeder creates exactly one persona account per role', function () {
    $this->seed(DatabaseSeeder::class);

    $personaDuplicates = User::query()
        ->where('email', 'like', '%@realestate.com')
        ->selectRaw('role, count(*) as total')
        ->groupBy('role')
        ->havingRaw('count(*) > 1')
        ->pluck('total', 'role');

    expect($personaDuplicates)->toBeEmpty();

    // Roles that no longer exist after the role consolidation.
    foreach (['client', 'user', 'seller', 'company_admin', 'real_estate_company', 'receptionist', 'housekeeping'] as $removedRole) {
        expect(User::where('role', $removedRole)->where('email', 'like', '%@realestate.com')->exists())->toBeFalse();
    }

    // Every non-persona account is either the documented corporate tenant or a
    // reserved `contact` counterparty created by factories and seeders.
    $nonPersonaRoles = User::query()
        ->where('email', 'not like', '%@realestate.com')
        ->distinct()
        ->pluck('role')
        ->sort()
        ->values()
        ->all();

    foreach ($nonPersonaRoles as $role) {
        expect($role)->toBeIn(['contact', 'company'], 'unexpected seeded non-persona role: '.$role);
    }

    expect(in_array('contact', $nonPersonaRoles, true))->toBeTrue();

    // Re-seeding must stay idempotent: no new accounts, no duplicate emails.
    $countAfterFirstSeed = User::count();
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe($countAfterFirstSeed);
    expect(User::query()->groupBy('email')->havingRaw('count(*) > 1')->exists())->toBeFalse();
});

test('every seeded persona signs in and opens their dashboard', function () {
    $this->seed(DatabaseSeeder::class);

    $portals = [
        'admin' => 'dashboard',
        'buyer' => 'buyer.dashboard',
        'tenant' => 'tenant.dashboard',
        'traveler' => 'guest.dashboard',
        'guest' => 'guest.dashboard',
        'property_owner' => 'role.portal',
        'host' => 'host.dashboard',
        'landlord' => 'role.portal',
        'agent' => 'agent.portal',
        'company' => 'company.dashboard',
        'developer' => 'developer.portal',
        'property_manager' => 'role.portal',
        'service_provider' => 'professional.dashboard',
        'moderator' => 'role.portal',
        'verification_officer' => 'role.portal',
        'verification_company' => 'verification_company.portal',
        'finance_officer' => 'finance.index',
        'customer_support' => 'role.portal',
        'cleaner' => 'cleaner.dashboard',
        'investor' => 'investor.dashboard',
        'campaign_officer' => 'campaign.dashboard',
    ];

    foreach ($portals as $role => $routeName) {
        $user = User::where('role', $role)->where('email', 'like', '%@realestate.com')->first();

        expect($user)->not->toBeNull("no seeded account for role {$role}");

        $this->assertTrue(
            auth()->attempt(['email' => $user->email, 'password' => 'secret123']),
            "seeded account {$user->email} cannot sign in with the documented password"
        );

        expect(auth()->user()->role)->toBe($role);

        $response = $this->actingAs($user)->get(route($routeName));

        $response->assertStatus(200);

        auth()->logout();
    }
});
