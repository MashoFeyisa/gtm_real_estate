<?php

use App\Models\Agent;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin cannot create user (admin) with a password that is too short', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/dashboard/users', [
        'name' => 'New Admin',
        'email' => 'new.admin@example.com',
        'password' => 'Pass1!',
        'role' => 'admin',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertDatabaseMissing('users', ['email' => 'new.admin@example.com']);
});

test('admin cannot create user (admin) with a password missing uppercase letter', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/dashboard/users', [
        'name' => 'New Admin',
        'email' => 'new.admin@example.com',
        'password' => 'password123!',
        'role' => 'admin',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertDatabaseMissing('users', ['email' => 'new.admin@example.com']);
});

test('admin cannot create user (admin) with a password missing number', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/dashboard/users', [
        'name' => 'New Admin',
        'email' => 'new.admin@example.com',
        'password' => 'Password!',
        'role' => 'admin',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertDatabaseMissing('users', ['email' => 'new.admin@example.com']);
});

test('admin cannot create user (admin) with a password missing symbol', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/dashboard/users', [
        'name' => 'New Admin',
        'email' => 'new.admin@example.com',
        'password' => 'Password123',
        'role' => 'admin',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertDatabaseMissing('users', ['email' => 'new.admin@example.com']);
});

test('admin can create user (admin) with a strong password', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/dashboard/users', [
        'name' => 'Strong Admin',
        'email' => 'strong.admin@example.com',
        'password' => 'Str0ng#Admin2026',
        'role' => 'admin',
    ]);

    $response->assertSessionHasNoErrors();
    $user = User::query()->where('email', 'strong.admin@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->role)->toBe('admin');
    expect(Hash::check('Str0ng#Admin2026', $user->password))->toBeTrue();
});

test('admin cannot create agent with a weak password', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/dashboard/agents', [
        'name' => 'Weak Agent',
        'email' => 'weak.agent@example.com',
        'password' => 'simple',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertDatabaseMissing('agents', ['email' => 'weak.agent@example.com']);
});

test('admin can create agent with a strong password', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/dashboard/agents', [
        'name' => 'Strong Agent',
        'email' => 'strong.agent@example.com',
        'phone' => '+251911223344',
        'password' => 'Str0ng#Agent2026',
    ]);

    $response->assertSessionHasNoErrors();
    $agent = Agent::query()->where('email', 'strong.agent@example.com')->first();
    expect($agent)->not->toBeNull();

    $account = $agent->account;
    expect($account)->not->toBeNull();
    expect(Hash::check('Str0ng#Agent2026', $account->password))->toBeTrue();
});

test('admin can create agent without password and an auto-generated strong password is created', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/dashboard/agents', [
        'name' => 'Auto Agent',
        'email' => 'auto.agent@example.com',
        'phone' => '+251911223344',
    ]);

    $response->assertSessionHasNoErrors();
    $agent = Agent::query()->where('email', 'auto.agent@example.com')->first();
    expect($agent)->not->toBeNull();

    $account = $agent->account;
    expect($account)->not->toBeNull();
    expect(strlen($account->password))->toBeGreaterThanOrEqual(16);
});

test('admin cannot update user with a weak password', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->put('/dashboard/users/'.$user->id, [
        'name' => 'Updated User',
        'email' => $user->email,
        'role' => 'admin',
        'password' => 'weak12',
    ]);

    $response->assertSessionHasErrors('password');
});

test('admin cannot update agent with a weak password', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $agent = Agent::create([
        'name' => 'Test Agent',
        'email' => 'test.agent@example.com',
    ]);

    $response = $this->actingAs($admin)->put('/dashboard/agents/'.$agent->id, [
        'name' => 'Test Agent Updated',
        'email' => 'test.agent@example.com',
        'password' => 'weakpassword',
    ]);

    $response->assertSessionHasErrors('password');
});

test('agent cannot update their portal profile with a weak password', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $agent = Agent::create([
        'name' => 'Agent Self',
        'email' => $user->email,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->put('/agent-portal/profile', [
        'name' => 'Agent Self',
        'email' => $user->email,
        'password' => 'weakpass',
    ]);

    $response->assertSessionHasErrors('password');
});

test('agent can update their portal profile with a strong password', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $agent = Agent::create([
        'name' => 'Agent Self',
        'email' => $user->email,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->put('/agent-portal/profile', [
        'name' => 'Agent Self',
        'email' => $user->email,
        'password' => 'N3w#StrongPass2026',
    ]);

    $response->assertSessionHasNoErrors();
    $user->refresh();
    expect(Hash::check('N3w#StrongPass2026', $user->password))->toBeTrue();
});
