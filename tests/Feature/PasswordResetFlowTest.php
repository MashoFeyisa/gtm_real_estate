<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('guests are shown the forgot password form', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('Forgot your password?', false)
        ->assertSee('name="email"', false);
});

test('a password reset link is emailed to a registered account', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertRedirect();

    Notification::assertSentTo($user, ResetPassword::class);
});

test('unknown accounts do not reveal whether they exist', function () {
    Notification::fake();

    $this->post(route('password.email'), ['email' => 'nobody@example.com'])
        ->assertSessionHasErrors('email');

    Notification::assertNothingSent();
});

test('the reset form renders for a valid emailed token', function () {
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertOk()
        ->assertSee('Choose a new password', false)
        ->assertSee($user->email);
});

test('a valid token resets the password and signs the user in', function () {
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewSecure123!',
        'password_confirmation' => 'NewSecure123!',
    ])->assertRedirect(route('client.portal'));

    expect(Hash::check('NewSecure123!', $user->fresh()->password))->toBeTrue();

    $this->assertAuthenticated();
    expect(auth()->id())->toBe($user->id);
});

test('an invalid token cannot change any password', function () {
    $user = User::factory()->create();
    $originalHash = $user->password;

    $this->post(route('password.update'), [
        'token' => 'bogus-token',
        'email' => $user->email,
        'password' => 'NewSecure123!',
        'password_confirmation' => 'NewSecure123!',
    ])->assertSessionHasErrors('email');

    expect($user->fresh()->password)->toBe($originalHash);
    $this->assertGuest();
});

test('weak passwords are rejected during reset', function () {
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('signed-in users are redirected away from the recovery forms', function () {
    $user = User::factory()->create(['role' => 'client']);

    $this->actingAs($user)->get(route('password.request'))
        ->assertRedirect(route('client.portal'));

    $this->actingAs($user)->get(route('password.reset', ['token' => 'irrelevant']))
        ->assertRedirect(route('client.portal'));
});
