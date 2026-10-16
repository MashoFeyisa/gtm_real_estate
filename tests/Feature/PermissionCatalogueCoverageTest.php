<?php

use App\Models\Permission;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

/**
 * Permission catalogue coverage.
 *
 * `hasPermission()` default-denies any key the catalogue does not define, so a
 * typo or a capability nobody catalogued fails silently. This scan collects
 * every permission key referenced by code (`hasPermission()`,
 * `hasAnyPermission()`, and `permission:` route middleware) and asserts each is
 * either seeded into the `permissions` table from `config/personas.php` or is a
 * declared alias of a catalogued key.
 */
uses(RefreshDatabase::class);

test('every permission key referenced in code exists in the catalogue or is a known alias', function () {
    $this->seed(RolePermissionSeeder::class);

    $aliases = config('personas.aliases', []);

    $known = Permission::query()->pluck('key')
        ->merge(array_keys($aliases))
        ->merge(array_values($aliases))
        ->unique();

    $references = collect();

    foreach ([base_path('app'), base_path('resources/views'), base_path('routes'), base_path('database/seeders')] as $root) {
        foreach (File::allFiles($root) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = File::get($file->getPathname());
            $relative = $file->getRelativePathname();

            preg_match_all("/has(?:Any)?Permission\s*\(\s*(?:\[([^\]]*)\]|'([^']+)'|\"([^\"]+)\")/s", $contents, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $blob = $match[1] ?: ($match[2] ?: ($match[3] ?? ''));

                preg_match_all("/['\"]([A-Za-z0-9_.]+)['\"]/", $blob, $keys);

                foreach ($keys[1] as $key) {
                    $references->push([$relative, $key]);
                }
            }

            preg_match_all('/permission:([A-Za-z0-9_.,|]+)/', $contents, $routeMatches);

            foreach ($routeMatches[1] as $group) {
                foreach (preg_split('/[,\|]/', $group) as $key) {
                    if ($key !== '') {
                        $references->push([$relative, $key]);
                    }
                }
            }
        }
    }

    expect($references)->not->toBeEmpty();

    $missing = $references
        ->reject(fn (array $reference) => $known->contains($reference[1]))
        ->values();

    $report = $missing->map(fn (array $reference) => "{$reference[0]}: {$reference[1]}")->implode("\n");

    expect($missing)->toBeEmpty(
        "Permission key(s) referenced in code but absent from the catalogue (config/personas.php) and the alias map:\n".$report
    );
});
