<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\File;
use SplFileInfo;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

/**
 * Views covered by the design-system guards: every unified dashboard role page,
 * the shared dashboard layout/partials, and the admin shell page (which extends
 * the unified layout). Legacy admin desk pages under resources/views/dashboard/**
 * predate the unified shell and are excluded (see docs/UI_CONTRAST_REPORT.md).
 *
 * @return list<string>
 */
function gtmDashboardViewFiles(): array
{
    $files = array_map(
        fn (SplFileInfo $file) => $file->getPathname(),
        File::allFiles(resource_path('views/dashboards')),
    );

    $files[] = resource_path('views/dashboard.blade.php');

    sort($files);

    return $files;
}

test('no unified dashboard view contains hard-coded colors', function () {
    $violations = [];

    foreach (gtmDashboardViewFiles() as $path) {
        $contents = file_get_contents($path);

        preg_match_all('/#[0-9a-fA-F]{3,8}\b/', $contents, $hexMatches);
        preg_match_all('/\b(?:rgb|hsl)a?\(\s*[\d.]/', $contents, $rgbMatches);
        preg_match_all('/(?:bg|text|border|ring|divide|fill|stroke|from|via|to|placeholder|decoration|accent|caret|outline|shadow)-(?:white|black|slate-[0-9]|gray-[0-9]|zinc-[0-9]|neutral-[0-9]|stone-[0-9]|red-[0-9]|orange-[0-9]|amber-[0-9]|yellow-[0-9]|lime-[0-9]|green-[0-9]|emerald-[0-9]|teal-[0-9]|cyan-[0-9]|sky-[0-9]|blue-[0-9]|indigo-[0-9]|violet-[0-9]|purple-[0-9]|fuchsia-[0-9]|pink-[0-9]|rose-[0-9])\b/', $contents, $paletteMatches);

        $groups = [
            'hex literal' => $hexMatches[0],
            'rgb()/hsl() color' => $rgbMatches[0],
            'palette utility' => $paletteMatches[0],
        ];

        foreach ($groups as $type => $matches) {
            foreach (array_unique($matches) as $match) {
                $violations[] = sprintf('%s: %s (%s)', str_replace(base_path().'/', '', $path), $match, $type);
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Dashboard views must use shared design tokens instead of hard-coded colors:\n".implode("\n", $violations)
    );
});

test('no unified dashboard view renders a language switcher', function () {
    $pattern = '/locale\.switch|language[-_]switcher|switch[-_]language|topbar-lang|app_locale/i';

    $violations = [];

    foreach (gtmDashboardViewFiles() as $path) {
        if (preg_match_all($pattern, file_get_contents($path), $matches)) {
            $violations[] = str_replace(base_path().'/', '', $path).': '.implode(', ', array_unique($matches[0]));
        }
    }

    expect($violations)->toBeEmpty(
        "The dashboard language selector was removed on purpose (locale is remembered via session/cookie):\n".implode("\n", $violations)
    );
});

test('no unified dashboard sidebar declares a duplicate section menu item', function (string $role) {
    $user = User::factory()->create(['role' => $role]);
    if ($role === 'agent') {
        $user->ensureAgentProfile();
    }

    $response = $this->actingAs($user)->get($user->dashboard_url);
    $response->assertOk();

    $content = $response->getContent();
    preg_match('/<aside[^>]*class="[^"]*dashboard-sidebar[^"]*"[^>]*>(.*?)<\/aside>/s', $content, $sidebarMatch);
    $sidebarHtml = $sidebarMatch[1] ?? '';
    expect($sidebarHtml)->not->toBe('');

    // Menu items are anchor tags carrying the nav-section-link marker; the avatar
    // profile chip is intentionally excluded from the duplicate check.
    preg_match_all('/<a\s[^>]*>/', $sidebarHtml, $tagMatches);
    $targets = [];
    foreach ($tagMatches[0] as $tag) {
        if (str_contains($tag, 'nav-section-link') && preg_match('/data-target-section="([^"]+)"/', $tag, $m)) {
            $targets[] = $m[1];
        }
    }

    $counts = array_count_values($targets);
    $duplicates = array_filter($counts, fn (int $count) => $count > 1);

    expect($duplicates)->toBeEmpty(
        "Role [{$role}] sidebar declares duplicate menu items for the same section: ".json_encode($duplicates)
    );
})->with([
    'buyer',
    'tenant',
    'seller',
    'host',
    'agent',
    'company',
    'developer',
    'property_manager',
    'cleaner',
    'moderator',
    'verification_officer',
    'campaign_officer',
    'finance_officer',
    'customer_support',
    'investor',
    'guest',
]);
