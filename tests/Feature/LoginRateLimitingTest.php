<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('repeated failed logins are throttled after five attempts', function () {
    $user = User::factory()->create(['password' => Hash::make('WrongPassword123!')]);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'definitely-the-wrong-password',
        ])->assertSessionHasErrors('email');
    }

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'definitely-the-wrong-password',
    ])->assertStatus(429);
});

test('a valid login still works within the rate limit window', function () {
    $user = User::factory()->create(['password' => Hash::make('CorrectPassword123!')]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'CorrectPassword123!',
    ])->assertRedirect();

    $this->assertAuthenticatedAs($user);
});

test('guests are redirected to login from the agent portal', function () {
    $this->from('/agent-portal')
        ->get('/agent-portal')
        ->assertRedirect(route('login'))
        ->assertSessionHas('url.intended');
});

test('authenticated non-agents are still forbidden from the agent portal', function () {
    $client = User::factory()->create(['role' => 'client']);

    $this->actingAs($client)->get('/agent-portal')->assertForbidden();
});
