<?php

use App\Models\User;
use App\Support\DemoData;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

/**
 * P0 security: fabricated demo data is a local-only convenience.
 *
 * The demo accounts share one public password and the login page offers them on
 * a one-click quick fill. Both must disappear outside a local environment, and
 * production seeding must refuse rather than create accounts anyone can use.
 */
test('demo data is allowed locally and in tests but not in production', function () {
    $this->app['env'] = 'local';
    expect(DemoData::allowed())->toBeTrue();

    $this->app['env'] = 'testing';
    expect(DemoData::allowed())->toBeTrue();

    $this->app['env'] = 'production';
    expect(DemoData::allowed())->toBeFalse();
});

test('production seeding refuses to create the demo accounts', function () {
    $this->app['env'] = 'production';

    $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])
        ->assertSuccessful();

    expect(User::where('email', 'like', '%@realestate.com')->count())->toBe(0)
        ->and(User::where('email', 'admin@realestate.com')->exists())->toBeFalse();
});

test('local seeding still creates the canonical demo accounts', function () {
    $this->app['env'] = 'local';

    $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])
        ->assertSuccessful();

    expect(User::where('email', 'admin@realestate.com')->exists())->toBeTrue();
});

test('the login quick-fill is hidden in production', function () {
    $this->app['env'] = 'production';

    $this->get('/login')
        ->assertOk()
        ->assertDontSee('Quick Fill')
        ->assertDontSee('admin@realestate.com');
});

test('the login quick-fill is shown locally', function () {
    $this->app['env'] = 'local';

    $this->get('/login')
        ->assertOk()
        ->assertSee('Quick Fill');
});

test('the demo-account command refuses to run outside local', function () {
    $this->app['env'] = 'production';

    $this->artisan('demo:user-accounts')
        ->assertExitCode(1);
});

test('the demo-reset command refuses to run outside local', function () {
    $this->app['env'] = 'production';

    $this->artisan('demo:reset', ['--no-seed' => true])
        ->assertExitCode(1);
});

test('the .env.example template does not default to a local environment', function () {
    $template = file_get_contents(base_path('.env.example'));

    expect($template)->toContain('APP_ENV=production')
        ->and($template)->not->toContain('APP_ENV=local');
});

test('demo:purge refuses to run while the demo dataset is allowed', function () {
    $this->app['env'] = 'local';

    $this->artisan('demo:purge')->assertExitCode(1);
});

test('demo:purge removes demo accounts and keeps real accounts', function () {
    $demo = User::factory()->create([
        'email' => 'admin@realestate.com',
        'is_demo' => true,
        'role' => 'admin',
    ]);

    $flagged = User::factory()->create([
        'email' => 'leftover.demo@example.com',
        'is_demo' => true,
    ]);

    $real = User::factory()->create([
        'email' => 'real.customer@example.com',
        'is_demo' => false,
    ]);

    $this->artisan('demo:purge', ['--force' => true])->assertSuccessful();

    expect(User::withTrashed()->findOrFail($demo->id)->trashed())->toBeTrue()
        ->and(User::withTrashed()->findOrFail($flagged->id)->trashed())->toBeTrue()
        ->and(User::withTrashed()->findOrFail($real->id)->trashed())->toBeFalse();
});

test('a purged demo account can no longer sign in with the shared password', function () {
    User::factory()->create([
        'email' => 'finance@realestate.com',
        'password' => Hash::make('secret123'),
        'is_demo' => true,
        'role' => 'finance_officer',
    ]);

    $this->artisan('demo:purge', ['--force' => true])->assertSuccessful();

    $this->post('/login', [
        'email' => 'finance@realestate.com',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    expect(User::where('email', 'finance@realestate.com')->exists())->toBeFalse();
});
