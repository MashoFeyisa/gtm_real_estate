<?php

use App\Support\MigrationStatus;
use Database\Seeders\FinanceRateSeeder;
use Database\Seeders\RolePermissionSeeder;

/**
 * `app:preflight` is the guard against the dev-database drift that makes a real
 * (MySQL) page fail while the in-memory test suite stays green.
 */
test('migration status is empty on a fully migrated database', function () {
    expect(MigrationStatus::pending())->toBe([]);
});

test('migration status reports a migration file that has not been run', function () {
    $path = database_path('migrations/9999_99_99_999999_zzz_preflight_probe.php');

    file_put_contents(
        $path,
        '<?php return new class extends Illuminate\\Database\\Migrations\\Migration { public function up(): void {} public function down(): void {} };'
    );

    try {
        expect(MigrationStatus::pending())->toContain('9999_99_99_999999_zzz_preflight_probe');
    } finally {
        @unlink($path);
    }

    expect(MigrationStatus::pending())->toBe([]);
});

test('preflight passes when the database is migrated and seeded', function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(FinanceRateSeeder::class);

    $this->artisan('app:preflight')->assertExitCode(0);
});

test('preflight fails when the permission catalogue has not been seeded', function () {
    // A freshly migrated database has no permission rows at all.
    $this->artisan('app:preflight')->assertExitCode(1);
});

test('preflight fails when a migration has not been run', function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(FinanceRateSeeder::class);

    $path = database_path('migrations/9999_99_99_999999_zzz_preflight_probe.php');

    file_put_contents(
        $path,
        '<?php return new class extends Illuminate\\Database\\Migrations\\Migration { public function up(): void {} public function down(): void {} };'
    );

    try {
        $this->artisan('app:preflight')->assertExitCode(1);
    } finally {
        @unlink($path);
    }
});
