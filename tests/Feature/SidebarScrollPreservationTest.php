<?php

use App\Models\Agent;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin dashboard sidebar has isolated sticky scroll area, skeleton, preserver, and 5 valid menu items without scroll jumps', function () {
    $admin = User::create([
        'name' => 'Sidebar Test Admin',
        'email' => 'admin.scroll@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertOk();

    // 1. Sticky isolated 100dvh scroll area
    $response->assertSee('sidebar-scroll-area');
    $response->assertSee('position: sticky; top: 0; height: 100dvh; overflow-y: auto;');

    // 2. Stable skeleton placeholder
    $response->assertSee('sidebar-skeleton');

    // 3. Scroll preservation script
    $response->assertSee('sidebar-scroll-preserver');
    $response->assertSee('sessionStorage.getItem');
    $response->assertSee('sessionStorage.setItem');

    // 4. Test 5 distinct menu items using proper data-target-section links without href="#"
    $menuItems = ['overview', 'users', 'properties', 'orders', 'listings'];
    foreach ($menuItems as $item) {
        $response->assertSee('data-target-section="'.$item.'"', false);
    }
    // Check sidebar does not have unhandled href="#"
    $html = $response->getContent();
    preg_match('/<aside[^>]*>(.*?)<\/aside>/s', $html, $matches);
    $sidebarHtml = $matches[1] ?? '';
    expect($sidebarHtml)->not()->toContain('href="#"');

    // 5. Ensure scrollIntoView / scrollTo is NOT invoked in dashboard section switching
    $html = $response->getContent();
    expect($html)->not()->toContain('targetPanel.scrollIntoView');
    expect($html)->not()->toContain('panel.scrollIntoView');
});

test('agent portal sidebar has isolated sticky scroll area, skeleton, preserver, and 5 valid menu items without scroll jumps', function () {
    $user = User::create([
        'name' => 'Sidebar Test Agent User',
        'email' => 'agent.scroll@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'agent',
    ]);

    $agent = Agent::create([
        'user_id' => $user->id,
        'name' => 'Sidebar Test Agent',
        'email' => 'agent.scroll@example.com',
        'phone' => '+251911000111',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('agent.portal'));

    $response->assertOk();

    // 1. Sticky isolated 100dvh scroll area
    $response->assertSee('sidebar-scroll-area');
    $response->assertSee('position: sticky; top: 0; height: 100dvh; overflow-y: auto;');

    // 2. Stable skeleton placeholder
    $response->assertSee('sidebar-skeleton');

    // 3. Scroll preservation script
    $response->assertSee('sidebar-scroll-preserver');

    // 4. Test 5 distinct menu items
    $menuItems = ['overview', 'leads-pipeline', 'viewings', 'offers-negotiations', 'listings'];
    foreach ($menuItems as $item) {
        $response->assertSee('data-target-section="'.$item.'"', false);
    }
    // Check sidebar does not have unhandled href="#"
    $html = $response->getContent();
    preg_match('/<aside[^>]*>(.*?)<\/aside>/s', $html, $matches);
    $sidebarHtml = $matches[1] ?? '';
    expect($sidebarHtml)->not()->toContain('href="#"');

    // 5. Ensure scrollIntoView is NOT invoked in agent section switching
    $html = $response->getContent();
    expect($html)->not()->toContain('targetPanel.scrollIntoView');
    expect($html)->not()->toContain('editPanel.scrollIntoView');
});

test('receptionist dashboard sidebar has isolated sticky scroll area, skeleton, preserver, and 5 valid menu items without scroll jumps', function () {
    $property = Property::create([
        'title' => 'Bole Grand Luxury Guest House',
        'slug' => 'bole-grand-luxury-guest-house-scroll-test',
        'type' => 'stay',
        'property_category' => 'guest_house',
        'price' => 3500,
        'city' => 'Addis Ababa',
        'address' => 'Bole Medhanealem, Atlas',
        'is_active' => true,
    ]);

    $receptionist = User::create([
        'name' => 'Sidebar Test Receptionist',
        'email' => 'receptionist.scroll@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'receptionist',
        'assigned_property_id' => $property->id,
    ]);

    $response = $this->actingAs($receptionist)->get(route('receptionist.dashboard'));

    $response->assertOk();

    // 1. Sticky isolated 100dvh scroll area
    $response->assertSee('sidebar-scroll-area');
    $response->assertSee('position: sticky; top: 0; height: 100dvh; overflow-y: auto;');

    // 2. Stable skeleton placeholder
    $response->assertSee('sidebar-skeleton');

    // 3. Scroll preservation script
    $response->assertSee('sidebar-scroll-preserver');

    // 4. Test 5 distinct menu items
    $menuItems = ['stay-listings', 'check-ins', 'in-house', 'check-outs', 'services'];
    foreach ($menuItems as $item) {
        $response->assertSee('data-target-section="'.$item.'"', false);
    }
    // Check sidebar does not have unhandled href="#"
    $html = $response->getContent();
    preg_match('/<aside[^>]*>(.*?)<\/aside>/s', $html, $matches);
    $sidebarHtml = $matches[1] ?? '';
    expect($sidebarHtml)->not()->toContain('href="#"');

    // 5. Ensure scrollIntoView is NOT invoked
    $html = $response->getContent();
    expect($html)->not()->toContain('targetPanel.scrollIntoView');
    expect($html)->not()->toContain('formCard.scrollIntoView');
});

test('client portal sidebar has isolated sticky scroll area, skeleton, preserver, and 5 valid menu items without scroll jumps', function () {
    $client = User::create([
        'name' => 'Sidebar Test Client',
        'email' => 'client.scroll@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'client',
    ]);

    $response = $this->actingAs($client)->get(route('client.portal'));

    $response->assertOk();

    // 1. Sticky isolated 100dvh scroll area
    $response->assertSee('sidebar-scroll-area');
    $response->assertSee('position: sticky; top: 0; height: 100dvh; overflow-y: auto;');

    // 2. Stable skeleton placeholder
    $response->assertSee('sidebar-skeleton');

    // 3. Scroll preservation script
    $response->assertSee('sidebar-scroll-preserver');

    // 4. Test 5 distinct menu items
    $menuItems = ['overview', 'stays', 'reviews', 'offers', 'leases'];
    foreach ($menuItems as $item) {
        $response->assertSee('data-target-section="'.$item.'"', false);
    }
    // Check sidebar does not have unhandled href="#"
    $html = $response->getContent();
    preg_match('/<aside[^>]*>(.*?)<\/aside>/s', $html, $matches);
    $sidebarHtml = $matches[1] ?? '';
    expect($sidebarHtml)->not()->toContain('href="#"');

    // 5. Ensure scrollTo top is NOT invoked
    $html = $response->getContent();
    expect($html)->not()->toContain('window.scrollTo({ top: 0, behavior: \'smooth\' })');
});
