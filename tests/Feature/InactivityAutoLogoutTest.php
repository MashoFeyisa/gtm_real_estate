<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Admin Inactivity',
        'email' => 'admin-inactive@example.com',
        'password' => Hash::make('Secret123!@#'),
        'role' => 'admin',
    ]);
});

test('authenticated user is not logged out after inactivity as auto logout is disabled', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['last_activity_time' => time() - 3600])
        ->get(route('dashboard'));

    $response->assertOk();
    $this->assertAuthenticatedAs($this->admin);
});

test('ajax request from user with past activity time remains authenticated', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['last_activity_time' => time() - 3600])
        ->getJson(route('dashboard'));

    $response->assertOk();
    $this->assertAuthenticatedAs($this->admin);
});

test('inactivity tracker warning modal is not rendered on dashboard', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertDontSee('id="inactivity-warning-modal"', false)
        ->assertDontSee('id="inactivity-auto-logout-form"', false);
});

test('lifestyle section in homepage has working hover and group-hover classes and theme css', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('A Curated Lifestyle Beyond Ordinary')
        ->assertSee('group rounded-3xl border border-[#d9cab3] bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#c5a059]', false)
        ->assertSee('group-hover:bg-[#102b25]', false)
        ->assertSee('group-hover:text-[#e7d8b7]', false)
        ->assertSee('.group:hover .group-hover\:text-\[\#e7d8b7\]', false)
        ->assertSee('.group:hover[class*="hover:border-[#c5a059]"]', false);
});
