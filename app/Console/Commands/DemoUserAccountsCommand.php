<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\User;
use App\Support\DemoData;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * Keeps the canonical demo accounts (the seeded <persona>@realestate.com
 * accounts, one per role) explicitly flagged so operator demo data can be told
 * apart from manually created accounts without guessing by email domain. The
 * same domain marker is used by users:cleanup, so both commands agree on which
 * accounts belong to the demo seed.
 */
#[Signature('demo:user-accounts
            {--sync : Flag the canonical persona accounts as demo accounts and clear stale flags elsewhere}
            {--repair : Re-converge password, verification and agent activation for the canonical accounts}')]
#[Description('Report, flag, or repair the canonical demo accounts (one per persona role)')]
class DemoUserAccountsCommand extends Command
{
    /**
     * Shared password used by DatabaseSeeder for every fabricated account.
     */
    private const DEMO_PASSWORD = 'secret123';

    /**
     * Email domain marking seeded persona accounts (see users:cleanup).
     */
    private const EMAIL_DOMAIN = 'realestate.com';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! DemoData::allowed()) {
            $this->error('Refusing to work on demo accounts outside a local environment.');

            return self::FAILURE;
        }

        $accounts = $this->canonicalAccounts();

        if ($this->option('sync')) {
            return $this->sync($accounts);
        }

        if ($this->option('repair')) {
            return $this->repair($accounts);
        }

        return $this->report($accounts);
    }

    /**
     * Live seeded persona accounts: one canonical account per role, addressed
     * as <something>@realestate.com by DatabaseSeeder.
     *
     * @return Collection<int, User>
     */
    private function canonicalAccounts(): Collection
    {
        return User::query()
            ->whereNull('deleted_at')
            ->where('email', 'like', '%@'.self::EMAIL_DOMAIN)
            ->orderBy('id')
            ->get();
    }

    /**
     * Flag every canonical account and clear the flag from anything else
     * (manually created accounts, protected duplicates, tombstones).
     *
     * @param  Collection<int, User>  $accounts
     */
    private function sync(Collection $accounts): int
    {
        foreach ($accounts as $user) {
            $user->forceFill(['is_demo' => true, 'demo_role_key' => $user->role])->save();
        }

        $cleared = User::query()
            ->where('is_demo', true)
            ->where('email', 'not like', '%@'.self::EMAIL_DOMAIN)
            ->update(['is_demo' => false, 'demo_role_key' => null]);

        $this->info('Flagged '.count($accounts).' canonical demo accounts; cleared '.$cleared.' stale flags.');

        return self::SUCCESS;
    }

    /**
     * Restore demo-account credentials and activation that drifted from the
     * seeded state (password, verification, agent record activation).
     *
     * @param  Collection<int, User>  $accounts
     */
    private function repair(Collection $accounts): int
    {
        foreach ($accounts as $user) {
            $actions = [];

            if (! Hash::check(self::DEMO_PASSWORD, (string) $user->password)) {
                $user->forceFill(['password' => Hash::make(self::DEMO_PASSWORD)]);
                $actions[] = 'password';
            }

            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()]);
                $actions[] = 'verified';
            }

            if (! $user->is_demo) {
                $user->forceFill(['is_demo' => true, 'demo_role_key' => $user->role]);
                $actions[] = 'flagged';
            }

            if ($user->isDirty()) {
                $user->save();
            }

            if ($user->role === 'agent') {
                $blocked = Agent::query()->where('user_id', $user->id)->where('is_active', false)->exists();

                if ($blocked) {
                    Agent::query()->where('user_id', $user->id)->update(['is_active' => true]);
                    $actions[] = 'agent-reactivated';
                }
            }

            $this->line('  '.$user->email.': '.($actions === [] ? 'ok' : implode(', ', $actions)));
        }

        $this->info('Demo account repair complete.');

        return self::SUCCESS;
    }

    /**
     * Show the state of every canonical account without changing anything.
     *
     * @param  Collection<int, User>  $accounts
     */
    private function report(Collection $accounts): int
    {
        $rows = [];

        foreach ($accounts as $user) {
            $agentOk = $user->role === 'agent'
                ? (Agent::query()->where('user_id', $user->id)->where('is_active', true)->exists() ? 'yes' : 'BLOCKED')
                : '-';

            $rows[] = [
                $user->role,
                $user->email,
                $user->is_demo ? 'flagged' : 'unflagged',
                $user->deactivated_at ? 'deactivated' : 'active',
                Hash::check(self::DEMO_PASSWORD, (string) $user->password) ? 'ok' : 'DRIFTED',
                $agentOk,
            ];
        }

        $this->table(['Role', 'Email', 'Demo flag', 'Account', 'Password', 'Agent active'], $rows);

        return self::SUCCESS;
    }
}
