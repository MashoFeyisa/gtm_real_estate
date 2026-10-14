<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merges duplicate roles into a single surviving role: users, the user_roles
 * pivot and the role_permissions catalog all move to the target, and the
 * permission sets are union-merged so no capability is lost.
 *
 * Dry run (default / --dry-run) only reports. --force applies inside one
 * transaction and verifies that no row still references a removed role.
 */
#[Signature('roles:consolidate {--dry-run : Report the merge without changing anything} {--force : Apply the merge inside a transaction}')]
#[Description('Merge duplicate roles (client/user → buyer, seller → property_owner, ...) into one role')]
class RolesConsolidateCommand extends Command
{
    /**
     * source role => target role. Mirrors App\Console\Commands\UsersCleanupCommand.
     *
     * @var array<string, string>
     */
    private const MERGE_MAP = [
        'client' => 'buyer',
        'user' => 'buyer',
        'seller' => 'property_owner',
        'company_admin' => 'company',
        'real_estate_company' => 'company',
        'receptionist' => 'customer_support',
        'housekeeping' => 'cleaner',
    ];

    protected $description = 'Command description';

    /**
     * @return int
     */
    public function handle()
    {
        if ($this->option('force') && $this->option('dry-run')) {
            $this->error('Use either --dry-run or --force, not both.');

            return self::FAILURE;
        }

        // --force is the explicit opt-in; no extra prompt so the command works
        // non-interactively (php artisan roles:consolidate --force).
        $force = (bool) $this->option('force');

        $this->newLine();
        $this->info($force ? 'ROLE CONSOLIDATION APPLIED' : 'DRY RUN — no changes were made');
        $this->newLine();

        DB::transaction(function () use ($force) {
            foreach (self::MERGE_MAP as $from => $into) {
                $this->mergeRole($from, $into, $force);
            }
        });

        if ($force) {
            $this->newLine();
            $this->info('Role consolidation committed.');
            $this->verifyIntegrity();
        }

        return self::SUCCESS;
    }

    /**
     * Merge one role into another.
     */
    private function mergeRole(string $from, string $into, bool $force): void
    {
        $sourceRole = DB::table('roles')->where('slug', $from)->first();
        $targetRole = DB::table('roles')->where('slug', $into)->first();

        $users = DB::table('users')->where('role', $from)->count();
        $pivot = 0;
        $grants = 0;

        if ($sourceRole) {
            $pivot = DB::table('user_roles')->where('role_id', $sourceRole->id)->count();
            $grants = DB::table('role_permissions')->where('role_id', $sourceRole->id)->count();
        }

        if (! $targetRole) {
            $this->error("  {$from} → {$into}: target role is missing from the catalog — skipped.");

            return;
        }

        if ($users === 0 && $pivot === 0 && ($sourceRole === null || $grants === 0)) {
            $this->line("  {$from} → {$into}: nothing to move.");

            if ($force && $sourceRole) {
                DB::table('roles')->where('id', $sourceRole->id)->delete();
                $this->line("     removed empty catalog role {$from}");
            }

            return;
        }

        if ($users > 0) {
            if ($force) {
                DB::table('users')->where('role', $from)->update(['role' => $into]);
            }
            $this->line("  {$from} → {$into}: {$users} users moved.");
        }

        if ($pivot > 0) {
            if ($force) {
                $this->repointPivot((int) $sourceRole->id, (int) $targetRole->id);
            }
            $this->line("  {$from} → {$into}: {$pivot} user_roles rows repointed.");
        }

        if ($sourceRole && $grants > 0) {
            $added = $this->unionPermissions((int) $sourceRole->id, (int) $targetRole->id, $force);
            $note = $added === 0 ? 'permission sets identical' : 'nothing lost';

            $this->line("  {$from} → {$into}: {$added} permission keys added to {$into} ({$note}).");

            if ($force) {
                DB::table('role_permissions')->where('role_id', $sourceRole->id)->delete();
                DB::table('roles')->where('id', $sourceRole->id)->delete();
                $this->line("  {$from} → {$into}: catalog role {$from} removed (its grants were merged).");
            } else {
                $this->line("  {$from} → {$into}: catalog role {$from} would be removed.");
            }
        }
    }

    /**
     * Point pivot rows at the target role, skipping duplicates.
     */
    private function repointPivot(int $sourceRoleId, int $targetRoleId): void
    {
        $rows = DB::table('user_roles')->where('role_id', $sourceRoleId)->get();

        foreach ($rows as $row) {
            $exists = DB::table('user_roles')
                ->where('user_id', $row->user_id)
                ->where('role_id', $targetRoleId)
                ->exists();

            if ($exists) {
                DB::table('user_roles')->where('id', $row->id)->delete();

                continue;
            }

            DB::table('user_roles')->where('id', $row->id)->update(['role_id' => $targetRoleId]);
        }
    }

    /**
     * Copy every permission key the target lacks from the source role.
     *
     * @return int number of permission keys added to the target
     */
    private function unionPermissions(int $sourceRoleId, int $targetRoleId, bool $force): int
    {
        $sourceKeys = DB::table('role_permissions as rp')
            ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('rp.role_id', $sourceRoleId)
            ->pluck('p.id')
            ->all();

        $targetKeys = DB::table('role_permissions')
            ->where('role_id', $targetRoleId)
            ->pluck('permission_id')
            ->all();

        $missing = array_values(array_diff($sourceKeys, $targetKeys));

        if ($force && ! empty($missing)) {
            $rows = array_map(fn ($permissionId) => [
                'role_id' => $targetRoleId,
                'role' => DB::table('roles')->where('id', $targetRoleId)->value('name'),
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ], $missing);

            DB::table('role_permissions')->insert($rows);
        }

        return count($missing);
    }

    /**
     * No row may still reference a role that was merged away.
     */
    private function verifyIntegrity(): void
    {
        $removedSlugs = array_keys(self::MERGE_MAP);
        $problems = [];

        foreach ($removedSlugs as $slug) {
            $users = DB::table('users')->where('role', $slug)->count();

            if ($users > 0) {
                $problems[] = "users still on role {$slug}: {$users}";
            }
        }

        $survivingRoleIds = DB::table('roles')->pluck('id')->all();
        $danglingPivot = DB::table('user_roles')->whereNotIn('role_id', $survivingRoleIds)->count();
        $danglingGrants = DB::table('role_permissions')->whereNotIn('role_id', $survivingRoleIds)->count();

        if ($danglingPivot > 0) {
            $problems[] = "user_roles rows pointing at removed roles: {$danglingPivot}";
        }

        if ($danglingGrants > 0) {
            $problems[] = "role_permissions rows pointing at removed roles: {$danglingGrants}";
        }

        if (empty($problems)) {
            $this->info('Integrity check: no users, pivot rows or grants left on merged-away roles.');

            return;
        }

        $this->error('Integrity check found problems:');
        foreach ($problems as $problem) {
            $this->line('  - '.$problem);
        }
    }
}
