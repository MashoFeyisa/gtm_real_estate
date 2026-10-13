<?php

use App\Models\Agent;
use App\Models\User;

test('logging out as an admin redirects to home landing page', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->post('/logout');

    $response->assertRedirect(route('home'));
    $this->assertGuest();
});

test('logging out as an agent redirects to home landing page', function () {
    $account = User::factory()->create([
        'name' => 'Agent User',
        'email' => 'agent@example.com',
        'role' => 'agent',
    ]);

    $agent = Agent::create([
        'name' => 'Agent User',
        'email' => 'agent@example.com',
        'phone' => '+251911000000',
        'user_id' => $account->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($account)->post('/logout');

    $response->assertRedirect(route('home'));
    $this->assertGuest();
});

test('login page contains password visibility toggle with eye and eye-slash icons', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('id="toggle-password"', false);
    $response->assertSee('id="eye-icon"', false);
    $response->assertSee('id="eye-slash-icon"', false);
});

test('login returns a client to the same-site action supplied by the auth prompt', function () {
    $client = User::factory()->create([
        'email' => 'client.redirect@example.com',
        'password' => bcrypt('password123'),
        'role' => 'client',
    ]);

    $this->get(route('login', ['redirect' => '/properties/saved-listing']))
        ->assertOk();

    $response = $this->post(route('login'), [
        'email' => $client->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect('/properties/saved-listing');
});

test('login ignores external redirect destinations supplied by the auth prompt', function () {
    $client = User::factory()->create([
        'email' => 'client.safe-redirect@example.com',
        'password' => bcrypt('password123'),
        'role' => 'client',
    ]);

    $this->get(route('login', ['redirect' => 'https://external.example/']))
        ->assertOk();

    $response = $this->post(route('login'), [
        'email' => $client->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('client.portal'));
});

test('agent creation form on dashboard displays Portal Password without the word optional', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin.agentform@example.com',
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard', ['section' => 'users']));

    $response->assertOk();
    $response->assertSee('Portal Password');
    $response->assertDontSee('Portal Password (optional)');
});
