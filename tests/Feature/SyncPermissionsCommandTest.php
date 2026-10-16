<?php

use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Deploys run `php artisan permissions:sync --force` after migrating so a
 * permission added to config/personas.php since the last release is never
 * default-denied because its catalogue row was never seeded.
 */
test('permissions:sync seeds the catalogue and is idempotent', function () {
    expect(Permission::query()->count())->toBe(0);

    $this->artisan('permissions:sync')->assertSuccessful();

    $seeded = Permission::query()->count();
    expect($seeded)->toBeGreaterThan(0);
    expect(Permission::query()->where('key', 'payments.manage')->exists())->toBeTrue();

    $this->artisan('permissions:sync')->assertSuccessful();

    expect(Permission::query()->count())->toBe($seeded);
});
