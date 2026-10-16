<?php

namespace App\Console\Commands;

use App\Support\DemoData;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Full demo reset: seed → consolidate roles → one account per role.
 *
 * Seeding alone can never leave a second account for a role, but a database
 * that already ran the cleanup keeps soft-deleted tombstones whose persona
 * emails were anonymized, so re-seeding creates fresh rows. Running the
 * cleanup again after seeding converges back to exactly one live account per
 * role — which is what this command does.
 */
#[Signature('demo:reset {--no-seed : Skip the seeder and only re-apply the cleanup}')]
#[Description('Seed, consolidate duplicate roles, and keep exactly one account per role')]
class DemoResetCommand extends Command
{
    protected $description = 'Command description';

    /**
     * @return int
     */
    public function handle()
    {
        if (! DemoData::allowed()) {
            $this->error('Refusing to build the demo dataset outside a local environment.');

            return self::FAILURE;
        }

        if (! $this->option('no-seed')) {
            $this->info('Seeding database (idempotent — no duplicate personas on re-run).');
            Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--no-interaction' => true], $this->output);
        }

        $this->info('Consolidating duplicate roles.');
        Artisan::call('roles:consolidate', ['--force' => true, '--no-interaction' => true], $this->output);

        $this->info('Applying the one-account-per-role cleanup.');
        Artisan::call('users:cleanup', ['--force' => true, '--no-interaction' => true], $this->output);

        $this->info('Flagging the canonical demo accounts.');
        Artisan::call('demo:user-accounts', ['--sync' => true, '--no-interaction' => true], $this->output);

        $this->newLine();
        $this->info('Demo reset complete. Verify with:');
        $this->line('  php artisan tinker --execute \'DB::table("users")->whereNull("deleted_at")->selectRaw("role, count(*) c")->groupBy("role")->get();\'');

        return self::SUCCESS;
    }
}
