<?php

use App\Models\User;

test('home page shows blog and news sections', function () {
    $response = $this->get('/app');

    $response->assertOk();
    $response->assertSee('Latest News');
    $response->assertSee('Blog');
});

test('workers attendance page has been removed', function () {
    $response = $this->get('/attendance');

    $response->assertNotFound();
});

test('dashboard redirects unauthenticated users to login', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('admin user can login with database credentials', function () {
    User::factory()->create([
        'email' => 'admin@realestate.com',
        'password' => bcrypt('secret123'),
        'role' => 'admin',
    ]);

    $response = $this->post('/login', [
        'email' => 'admin@realestate.com',
        'password' => 'secret123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
});
