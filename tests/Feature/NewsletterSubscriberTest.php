<?php

use App\Models\NewsletterSubscriber;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('client can subscribe to newsletter and be counted as a happy buyer', function () {
    $response = $this->from('/')
        ->post('/newsletter/subscribe', [
            'email' => 'client@example.com',
        ]);

    $response->assertRedirect('/#newsletter')
        ->assertSessionHas('newsletter_success');

    $this->assertDatabaseHas('newsletter_subscribers', [
        'email' => 'client@example.com',
    ]);
});

test('client can subscribe via ajax and receive updated happy buyers count', function () {
    $response = $this->postJson('/newsletter/subscribe', [
        'email' => 'ajaxclient@example.com',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'is_new' => true,
        ])
        ->assertJsonStructure(['success', 'is_new', 'message', 'count']);

    expect($response->json('count'))->toBe(1);
});

test('subscribing with an existing email does not duplicate or artificially inflate count', function () {
    NewsletterSubscriber::create([
        'email' => 'existing@example.com',
    ]);

    $response = $this->postJson('/newsletter/subscribe', [
        'email' => 'EXISTING@example.com',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'is_new' => false,
        ]);

    expect(NewsletterSubscriber::where('email', 'existing@example.com')->count())->toBe(1);
});

test('subscribing with invalid email fails validation', function () {
    $response = $this->postJson('/newsletter/subscribe', [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('home page displays dynamic happy buyers count based on subscribers', function () {
    NewsletterSubscriber::create(['email' => 'buyer1@example.com']);
    NewsletterSubscriber::create(['email' => 'buyer2@example.com']);
    NewsletterSubscriber::create(['email' => 'buyer3@example.com']);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('3');
    $response->assertSee('happy buyers');

    $appResponse = $this->get('/app');
    $appResponse->assertOk();
    $appResponse->assertSee('3');
    $appResponse->assertSee('happy buyers');
});

test('admin can delete a newsletter subscriber from dashboard', function () {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin-sub@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $subscriber = NewsletterSubscriber::create(['email' => 'remove-me@example.com']);

    $response = $this->actingAs($admin)
        ->delete(route('dashboard.subscribers.destroy', $subscriber));

    $response->assertRedirect(route('dashboard', ['tab' => 'subscribers']))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('newsletter_subscribers', [
        'id' => $subscriber->id,
    ]);
});

test('admin can configure happy buyers base count offset', function () {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin-settings@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    NewsletterSubscriber::create(['email' => 'buyer1@example.com']);

    $response = $this->actingAs($admin)
        ->post(route('dashboard.settings.update'), [
            'site_name' => 'GTP Real Estate',
            'contact_email' => 'gtmrealstate@gmail.com',
            'contact_phone' => '+251 993722346',
            'contact_address' => 'Harar, Ethiopia',
            'happy_buyers_base_count' => 1200,
        ]);

    $response->assertRedirect(route('dashboard', ['section' => 'settings']));
    expect((int) SiteSetting::get('happy_buyers_base_count'))->toBe(1200);

    $homeResponse = $this->get('/');
    $homeResponse->assertOk();
    $homeResponse->assertSee('1,201');
    $homeResponse->assertSee('happy buyers');
});
