<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Compares the migration files on disk with the `migrations` table.
 *
 * The dev database silently drifting behind the code is how a page starts
 * failing with "Unknown column ..." while the SQLite test suite stays green, so
 * both `app:preflight` and the local boot warning ask this one helper.
 */
class MigrationStatus
{
    /**
     * Migration file basenames (without `.php`) that have not been run.
     *
     * @return list<string>
     */
    public static function pending(): array
    {
        $files = collect(glob(database_path('migrations/*.php')) ?: [])
            ->map(fn (string $path): string => pathinfo($path, PATHINFO_FILENAME))
            ->values();

        try {
            // A missing table means a fresh database: every file is pending.
            if (! Schema::hasTable('migrations')) {
                return $files->all();
            }

            $ran = DB::table('migrations')->pluck('migration')->all();
        } catch (Throwable) {
            // The connection itself is broken; the database check reports that.
            return [];
        }

        return $files->diff($ran)->values()->all();
    }

    public static function hasPending(): bool
    {
        return self::pending() !== [];
    }
}
