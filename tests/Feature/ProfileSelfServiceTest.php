<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    $this->client = User::create([
        'name' => 'Client Hanna',
        'email' => 'hanna@example.com',
        'password' => Hash::make('password123'),
        'role' => 'user',
    ]);

    $this->financeOfficer = User::create([
        'name' => 'Finance Officer Dawit',
        'email' => 'dawit.finance@example.com',
        'password' => Hash::make('password123'),
        'role' => 'finance_officer',
    ]);
});

test('client updates their own name, password and profile photo', function () {
    $response = $this->actingAs($this->client)->post(route('profile.update'), [
        'name' => 'Hanna Tesfaye',
        'password' => 'NewPassw0rd!',
        'photo' => UploadedFile::fake()->image('hanna.jpg', 400, 400),
    ]);

    $response->assertRedirect(route('client.portal').'#profile');
    $response->assertSessionHas('success');

    $this->client->refresh();

    expect($this->client->name)->toBe('Hanna Tesfaye')
        ->and(Hash::check('NewPassw0rd!', $this->client->password))->toBeTrue()
        ->and($this->client->photo_path)->not->toBeNull();

    Storage::disk('public')->assertExists($this->client->photo_path);
    expect($this->client->photo_url)->toBe(asset('storage/'.$this->client->photo_path));
});

test('email and role cannot be changed through the profile form', function () {
    $this->actingAs($this->client)->post(route('profile.update'), [
        'name' => 'Client Hanna',
        'email' => 'attacker@example.com',
        'role' => 'admin',
    ])->assertRedirect(route('client.portal').'#profile');

    $this->client->refresh();

    expect($this->client->email)->toBe('hanna@example.com')
        ->and($this->client->role)->toBe('user');
});

test('updating the profile without a password keeps the current password', function () {
    $originalPassword = $this->client->password;

    $this->actingAs($this->client)->post(route('profile.update'), [
        'name' => 'Hanna Renamed',
        'password' => '',
    ])->assertRedirect(route('client.portal').'#profile');

    expect($this->client->refresh()->password)->toBe($originalPassword)
        ->and(Hash::check('password123', $this->client->password))->toBeTrue();
});

test('uploading a new profile photo replaces and deletes the previous file', function () {
    $this->actingAs($this->client)->post(route('profile.update'), [
        'name' => 'Client Hanna',
        'photo' => UploadedFile::fake()->image('first.jpg'),
    ]);

    $firstPhotoPath = $this->client->refresh()->photo_path;

    $this->actingAs($this->client)->post(route('profile.update'), [
        'name' => 'Client Hanna',
        'photo' => UploadedFile::fake()->image('second.jpg'),
    ]);

    $secondPhotoPath = $this->client->refresh()->photo_path;

    expect($secondPhotoPath)->not->toBe($firstPhotoPath);

    Storage::disk('public')->assertMissing($firstPhotoPath);
    Storage::disk('public')->assertExists($secondPhotoPath);
});

test('non-image uploads and weak passwords are rejected', function () {
    $this->actingAs($this->client)->post(route('profile.update'), [
        'name' => 'Client Hanna',
        'password' => 'weak',
        'photo' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors(['password', 'photo']);

    $this->client->refresh();

    expect($this->client->photo_path)->toBeNull()
        ->and(Hash::check('password123', $this->client->password))->toBeTrue();
});

test('finance officer edits their own profile and returns to the finance desk', function () {
    $response = $this->actingAs($this->financeOfficer)->post(route('profile.update'), [
        'name' => 'Dawit Bekele',
        'photo' => UploadedFile::fake()->image('dawit.png'),
    ]);

    $response->assertRedirect(route('finance.index').'#profile');
    $response->assertSessionHas('success');

    expect($this->financeOfficer->refresh()->name)->toBe('Dawit Bekele');
});

test('a profile update never touches another account', function () {
    $this->actingAs($this->client)->post(route('profile.update'), [
        'name' => 'Renamed Client',
        'email' => 'hijacked@example.com',
        'id' => $this->financeOfficer->id,
        'user_id' => $this->financeOfficer->id,
    ])->assertRedirect(route('client.portal').'#profile');

    expect($this->financeOfficer->refresh()->name)->toBe('Finance Officer Dawit')
        ->and($this->financeOfficer->email)->toBe('dawit.finance@example.com');
});

test('guests are redirected to login instead of updating a profile', function () {
    $this->post(route('profile.update'), ['name' => 'Anonymous'])->assertRedirect(route('login'));

    expect($this->client->refresh()->name)->toBe('Client Hanna');
});

test('the client portal and finance desk expose the profile section and current photo', function () {
    $this->actingAs($this->client)->post(route('profile.update'), [
        'name' => 'Client Hanna',
        'photo' => UploadedFile::fake()->image('hanna.jpg'),
    ]);

    $this->actingAs($this->client)
        ->get(route('client.portal'))
        ->assertOk()
        ->assertSee('data-section="profile"', false)
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee(asset('storage/'.$this->client->photo_path), false);

    $this->actingAs($this->financeOfficer)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertSee('data-target-section="profile"', false)
        ->assertSee('My Profile');
});

test('client can update and reset their portal hero background image', function () {
    // 1. Upload custom background
    $response = $this->actingAs($this->client)->post(route('profile.update'), [
        'name' => 'Client Hanna',
        'portal_bg' => UploadedFile::fake()->image('custom-banner.jpg', 1600, 900),
    ]);

    $response->assertRedirect(route('client.portal').'#profile');
    $this->client->refresh();

    expect($this->client->portal_bg_path)->not->toBeNull();
    Storage::disk('public')->assertExists($this->client->portal_bg_path);
    expect($this->client->portal_bg_url)->toBe(asset('storage/'.$this->client->portal_bg_path));

    // 2. Select preset background
    $this->actingAs($this->client)->post(route('profile.update'), [
        'name' => 'Client Hanna',
        'portal_bg_preset' => 'images/luxury/luxury-towers.jpg',
    ])->assertRedirect(route('client.portal').'#profile');

    $this->client->refresh();
    expect($this->client->portal_bg_path)->toBe('images/luxury/luxury-towers.jpg');
    expect($this->client->portal_bg_url)->toBe(asset('images/luxury/luxury-towers.jpg'));

    // 3. Reset background
    $this->actingAs($this->client)->post(route('profile.update'), [
        'name' => 'Client Hanna',
        'remove_portal_bg' => 1,
    ])->assertRedirect(route('client.portal').'#profile');

    $this->client->refresh();
    expect($this->client->portal_bg_path)->toBeNull();
    expect($this->client->portal_bg_url)->toBe(asset('images/luxury/hero-skyline.jpg'));
});
