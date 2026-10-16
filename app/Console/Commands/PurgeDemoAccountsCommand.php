<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\User;
use App\Support\DemoData;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Remove the fabricated demo accounts from a non-local environment.
 *
 * Seeding is already blocked outside local (see DemoData), but a database that
 * was seeded before that guard - or a backup restored into production - can
 * still carry the one-click accounts whose shared password is public. This
 * command finds them (the `is_demo` flag and the canonical `@realestate.com`
 * domain), breaks the shared password, and removes their access.
 */
#[Signature('demo:purge
            {--force : Purge even when the demo dataset is allowed (local/testing)}')]
#[Description('Remove the fabricated demo accounts from a non-local environment')]
class PurgeDemoAccountsCommand extends Command
{
    /**
     * Email domain marking the seeded persona accounts (see demo:user-accounts).
     */
    private const EMAIL_DOMAIN = 'realestate.com';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (DemoData::allowed() && ! $this->option('force')) {
            $this->error(
                'Refusing to purge demo accounts while the demo dataset is allowed '
                .'(APP_ENV='.app()->environment().'). Pass --force to override.'
            );

            return self::FAILURE;
        }

        $accounts = User::withTrashed()
            ->where(function ($query): void {
                $query->where('is_demo', true)
                    ->orWhere('email', 'like', '%@'.self::EMAIL_DOMAIN);
            })
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('No demo accounts found; nothing to purge.');

            return self::SUCCESS;
        }

        $purged = 0;

        foreach ($accounts as $account) {
            DB::transaction(function () use ($account): void {
                $account->roles()->detach();

                // Deactivate the linked agent/broker profile so it cannot sign in.
                Agent::query()->where('user_id', $account->id)->update(['is_active' => false]);

                if (! $account->trashed()) {
                    // Break the public shared password before removing access.
                    $account->forceFill(['password' => Hash::make(Str::random(40))])->save();
                    $account->delete();
                }
            });

            $purged++;
        }

        $this->info("Purged {$purged} demo account(s).");

        return self::SUCCESS;
    }
}
