<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Re-run the persona/permission catalog seeder.
 *
 * `RolePermissionSeeder` is idempotent (firstOrCreate / firstOrNew), so it is
 * safe to run on every deploy: it creates any permission added to
 * config/personas.php since the last release without touching existing grants.
 * Deploys must call this after `migrate` so a new permission key is never
 * default-denied in production because its row was never seeded.
 */
#[Signature('permissions:sync
            {--force : Run without confirmation in production}')]
#[Description('Sync the permission catalogue and default role grants from config/personas.php')]
class SyncPermissionsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to sync permissions in production without --force.');

            return self::FAILURE;
        }

        $before = Permission::query()->count();

        $this->call(RolePermissionSeeder::class);

        $after = Permission::query()->count();

        $this->info(sprintf(
            'Permission catalogue synced: %d -> %d catalogued keys.',
            $before,
            $after
        ));

        return self::SUCCESS;
    }
}
