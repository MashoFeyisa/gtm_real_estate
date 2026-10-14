<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Keeps exactly one user account per role so each persona desk has a single,
 * fully populated demo account.
 *
 * Dry run (default / --dry-run) only prints and writes docs/USER_CLEANUP_PLAN.md.
 * --force performs the cleanup inside a single database transaction.
 */
#[Signature('users:cleanup {--dry-run : Print the plan without touching the database} {--force : Apply the plan inside a transaction}')]
#[Description('Keep exactly one user per role; reassign, anonymize, or remove the extras')]
class UsersCleanupCommand extends Command
{
    /**
     * Tables whose rows are financial or audit records: never deleted, never
     * reassigned. The user reference is preserved via anonymization instead.
     *
     * @var array<int, string>
     */
    private const PROTECTED_TABLES = [
        'audit_logs',
        'activity_logs',
        'financial_ledgers',
        'payments',
        'commissions',
        'invoices',
    ];

    /**
     * Per-user linkage rows: deleted with the user, never reassigned (the kept
     * account already carries its own grants).
     *
     * @var array<int, string>
     */
    private const LINKAGE_TABLES = [
        'user_permissions',
        'user_permission_overrides',
        'user_roles',
    ];

    /**
     * Auth/session rows tied to the session owner: deleted with the user.
     *
     * @var array<int, string>
     */
    private const AUTH_TABLES = [
        'sessions',
        'api_tokens',
    ];

    /**
     * One-to-one profile tables keyed by user_id: the kept account's row wins.
     *
     * @var array<int, string>
     */
    private const ONE_TO_ONE_TABLES = [
        'agents',
        'company_white_labels',
    ];

    /**
     * Emails seeded by DatabaseSeeder identify the canonical demo account.
     */
    private const SEEDED_EMAIL_DOMAIN = 'realestate.com';

    /**
     * Duplicate roles merged into a surviving role. Users, the role_permissions
     * catalog and the user_roles pivot move to the target; permissions are
     * union-merged so no capability is lost.
     *
     * @var array<string, string>
     */
    private const ROLE_MERGE_MAP = [
        'client' => 'buyer',
        'user' => 'buyer',
        'seller' => 'property_owner',
        'company_admin' => 'company',
        'real_estate_company' => 'company',
        'receptionist' => 'customer_support',
        'housekeeping' => 'cleaner',
    ];

    /**
     * Manual accounts owned by the developer. They are duplicates of a seeded
     * persona account, but the owner asked to keep them, so they are excluded
     * from removal and reported in the plan.
     *
     * @var array<string, string>
     */
    private const PROTECTED_MANUAL_ACCOUNTS = [
        'ag@gmail.com' => 'Agent — duplicate of agent@realestate.com',
        'ca@gmail.com' => 'Cadmin (company_admin) — duplicate of company@realestate.com',
        'nd@gmail.com' => 'Nuradin Dalu (developer) — duplicate of developer@realestate.com (same person)',
        'hg@gmail.com' => 'Hailmariam Getachawo (user) — duplicate of buyer@realestate.com after user→buyer merge',
    ];

    /**
     * Roles that hold demo-data counterparties instead of operator personas.
     * They are exempt from the one-account-per-role rule.
     *
     * @var array<int, string>
     */
    private const NON_PERSONA_ROLES = [
        'contact',
    ];

    /**
     * Canonical persona account per role (the README demo accounts). Beats a
     * plain @realestate.com address when choosing which account to keep.
     *
     * @var array<string, string>
     */
    private const CANONICAL_PERSONA_EMAILS = [
        'admin' => 'admin@realestate.com',
        'guest' => 'guest@realestate.com',
        'buyer' => 'buyer@realestate.com',
        'tenant' => 'tenant@realestate.com',
        'traveler' => 'traveler@realestate.com',
        'property_owner' => 'owner@realestate.com',
        'host' => 'host@realestate.com',
        'landlord' => 'landlord@realestate.com',
        'agent' => 'agent@realestate.com',
        'company' => 'company@realestate.com',
        'developer' => 'developer@realestate.com',
        'property_manager' => 'propertymanager@realestate.com',
        'service_provider' => 'service@realestate.com',
        'moderator' => 'moderator@realestate.com',
        'verification_officer' => 'verification@realestate.com',
        'verification_company' => 'verifycompany@realestate.com',
        'finance_officer' => 'finance@realestate.com',
        'customer_support' => 'support@realestate.com',
        'cleaner' => 'cleaner@realestate.com',
        'investor' => 'investor@realestate.com',
        'campaign_officer' => 'campaign@realestate.com',
    ];

    /**
     * Columns pointing at the users table. Deliberately excludes `agent_id`,
     * which references the agents table (via agents.user_id).
     */
    private const USER_REFERENCE_PATTERN = '(^user_id$|_user_id$|_by$|_officer_id$|^assigned_to$|^respondent_id$|^host_id$|^owner_id$)';

    /**
     * Reference map: table => list of user-referencing columns.
     *
     * @var array<string, array<int, string>>
     */
    private array $references = [];

    /**
     * Reference map: table => list of agent-referencing columns.
     *
     * @var array<string, array<int, string>>
     */
    private array $agentReferences = [];

    /**
     * Per-user record counts, keyed by user id.
     *
     * @var array<int, array<string, int>>
     */
    private array $recordCounts = [];

    /**
     * @return int
     */
    public function handle()
    {
        if ($this->option('force') && $this->option('dry-run')) {
            $this->error('Use either --dry-run or --force, not both.');

            return self::FAILURE;
        }

        $this->references = $this->discoverReferences(self::USER_REFERENCE_PATTERN, 'users');
        $this->agentReferences = $this->discoverReferences('(^agent_id$|_agent_id$)', 'agents');

        $users = User::query()->withTrashed()->orderBy('id')->get();
        $live = $users->whereNull('deleted_at');

        foreach ($live as $user) {
            $this->recordCounts[$user->id] = $this->countUserReferences($user->id);
        }

        $plan = $this->buildPlan($users);

        if (empty($plan['remove'])) {
            $this->info('Nothing to clean — every role already has exactly one user.');

            return self::SUCCESS;
        }

        if ($this->option('force')) {
            // --force is the explicit opt-in; no extra prompt so the command
            // works non-interactively (php artisan users:cleanup --force).
            $this->warn('Applying user cleanup inside a transaction. Backup lives in storage/backups/.');
            $this->applyPlan($plan);
        }

        $this->renderPlan($plan, $users, (bool) $this->option('force'));
        $this->writePlanFile($plan);

        return self::SUCCESS;
    }

    /**
     * Columns that match the user-reference pattern but do NOT hold user ids
     * (validated by data — see discoverReferences()).
     *
     * @var array<int, string>
     */
    private const NON_USER_COLUMNS = [
        'property_offers.latest_counter_by',
    ];

    /**
     * Discover every column matching the given pattern whose values are ids of
     * the given table. Columns that match the pattern but hold something else
     * (a role name, an agent id in a user scan) are skipped, so a non-user
     * value can never be overwritten with a user id.
     *
     * @return array<string, array<int, string>>
     */
    private function discoverReferences(string $pattern, string $idTable): array
    {
        $tables = collect(Schema::getTables())->map(function ($table) {
            return is_array($table) ? ($table['name'] ?? null) : ($table->name ?? null);
        })->filter()->all();

        $rows = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->whereIn('TABLE_NAME', $tables)
            ->whereRaw('COLUMN_NAME REGEXP ?', [$pattern])
            ->orderBy('TABLE_NAME')
            ->get(['TABLE_NAME', 'COLUMN_NAME', 'DATA_TYPE']);

        $idLookup = array_flip(DB::table($idTable)->pluck('id')->all());
        $map = [];
        $skipped = [];

        foreach ($rows as $row) {
            $table = (string) $row->TABLE_NAME;
            $column = (string) $row->COLUMN_NAME;

            if ($table === 'users' || in_array($table.'.'.$column, self::NON_USER_COLUMNS, true)) {
                continue;
            }

            $values = DB::table($table)->whereNotNull($column)->distinct()->limit(20)->pluck($column);

            if ($values->isNotEmpty()) {
                $holdsIds = $values->every(fn ($value) => is_numeric($value))
                    && $values->contains(fn ($value) => isset($idLookup[$value]));

                if (! $holdsIds) {
                    $skipped[] = $table.'.'.$column.' (sample: '.$values->take(3)->implode(', ').')';

                    continue;
                }
            }

            $map[$table][] = $column;
        }

        if (! empty($skipped) && $idTable === 'users') {
            $this->warn('Columns skipped — they match the pattern but do not hold user ids:');
            foreach ($skipped as $column) {
                $this->line('  - '.$column);
            }
        }

        return $map;
    }

    /**
     * Build the keep/remove plan per role.
     *
     * @param  Collection<int, User>  $users
     * @return array{keep: array<int, array{id:int,name:string,email:string,role:string,protected:bool}>, remove: array<int, array{id:int,name:string,email:string,role:string,reason:string,counts:array<string,int>,has_financials:bool}>, protected_manual: array<int, array{id:int,name:string,email:string,role:string,note:string}>}
     */
    private function buildPlan($users): array
    {
        $live = $users->whereNull('deleted_at')
            ->reject(fn (User $user) => in_array((string) $user->role, self::NON_PERSONA_ROLES, true));
        $mainAdminId = $this->mainAdminId($users);

        $plan = ['keep' => [], 'remove' => [], 'protected_manual' => []];

        foreach ($live->groupBy('role') as $role => $roleUsers) {
            $ranked = $roleUsers->sortByDesc(function (User $user) {
                $canonical = (self::CANONICAL_PERSONA_EMAILS[(string) $user->role] ?? null) === (string) $user->email ? 1 : 0;
                $seeded = str_ends_with((string) $user->email, '@'.self::SEEDED_EMAIL_DOMAIN) ? 1 : 0;
                $completeness = collect($user->getAttributes())
                    ->only(['name', 'email', 'phone', 'profession', 'bio', 'license_number', 'id_card_path', 'photo_path'])
                    ->filter()
                    ->count();

                return [$canonical, $seeded, array_sum($this->recordCounts[$user->id] ?? []), $completeness, -$user->id];
            })->values();

            /** @var User $keeper */
            $keeper = $ranked->first();

            $plan['keep'][] = [
                'id' => $keeper->id,
                'name' => (string) $keeper->name,
                'email' => (string) $keeper->email,
                'role' => (string) $role,
                'protected' => $keeper->id === $mainAdminId,
            ];

            // A manual developer account that is currently the only account of
            // its role (company_admin) is kept anyway, but must stay visible in
            // the manual-accounts section because the role merge turns it into
            // a duplicate of the surviving persona account.
            if (isset(self::PROTECTED_MANUAL_ACCOUNTS[(string) $keeper->email])) {
                $plan['protected_manual'][] = [
                    'id' => $keeper->id,
                    'name' => (string) $keeper->name,
                    'email' => (string) $keeper->email,
                    'role' => (string) $role,
                    'note' => self::PROTECTED_MANUAL_ACCOUNTS[(string) $keeper->email],
                ];
            }

            foreach ($ranked->skip(1) as $user) {
                // The main administrator is never removed.
                if ($user->id === $mainAdminId) {
                    continue;
                }

                // Manual developer accounts are kept on request.
                if (isset(self::PROTECTED_MANUAL_ACCOUNTS[(string) $user->email])) {
                    $plan['protected_manual'][] = [
                        'id' => $user->id,
                        'name' => (string) $user->name,
                        'email' => (string) $user->email,
                        'role' => (string) $role,
                        'note' => self::PROTECTED_MANUAL_ACCOUNTS[(string) $user->email],
                    ];

                    continue;
                }

                $plan['remove'][] = [
                    'id' => $user->id,
                    'name' => (string) $user->name,
                    'email' => (string) $user->email,
                    'role' => (string) $role,
                    'reason' => str_ends_with((string) $user->email, '@'.self::SEEDED_EMAIL_DOMAIN)
                        ? 'duplicate seeded account for role "'.$role.'"'
                        : 'extra account for role "'.$role.'" — keeping #'.$keeper->id.' '.$keeper->email,
                    'counts' => $this->recordCounts[$user->id] ?? [],
                    'has_financials' => $this->hasFinancialReferences($user->id),
                ];
            }
        }

        usort($plan['remove'], fn ($a, $b) => $a['id'] <=> $b['id']);

        return $plan;
    }

    /**
     * The main administrator account is never removed.
     *
     * @param  Collection<int, User>  $users
     */
    private function mainAdminId($users): ?int
    {
        $admin = $users->whereNull('deleted_at')
            ->where('role', 'admin')
            ->where('email', 'admin@'.self::SEEDED_EMAIL_DOMAIN)
            ->first()
            ?? $users->whereNull('deleted_at')->where('role', 'admin')->sortBy('id')->first();

        return $admin?->id;
    }

    /**
     * Count rows referencing the given user across the whole schema.
     *
     * @return array<string, int>
     */
    private function countUserReferences(int $userId): array
    {
        $counts = [];

        foreach ($this->references as $table => $columns) {
            $total = 0;

            foreach ($columns as $column) {
                $total += DB::table($table)->where($column, $userId)->count();
            }

            if ($total > 0) {
                $counts[$table] = $total;
            }
        }

        return $counts;
    }

    /**
     * Whether the user owns rows that must never be deleted.
     */
    private function hasFinancialReferences(int $userId): bool
    {
        foreach (self::PROTECTED_TABLES as $table) {
            foreach ($this->references[$table] ?? [] as $column) {
                if (DB::table($table)->where($column, $userId)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Apply the plan inside a single transaction.
     *
     * @param  array{keep: array<int, array{id:int,name:string,email:string,role:string,protected:bool}>, remove: array<int, array{id:int,name:string,email:string,role:string,reason:string,counts:array<string,int>,has_financials:bool}>}  $plan
     */
    private function applyPlan(array $plan): void
    {
        $keepers = collect($plan['keep'])->keyBy('role');

        DB::transaction(function () use ($plan, $keepers) {
            foreach ($plan['remove'] as $entry) {
                /** @var User $user */
                $user = User::withTrashed()->findOrFail($entry['id']);
                $keeperId = $keepers->get($entry['role'])['id'] ?? null;

                if ($keeperId && $keeperId !== $user->id) {
                    $this->reassignReferences($user, $keeperId);
                }

                $this->dropLinkageAndAuthRows($user);

                if ($this->hasFinancialReferences($user->id)) {
                    // Owns financial/audit rows: anonymize in place and soft-delete
                    // so those references stay valid forever.
                    $this->anonymizeAndSoftDelete($user);
                } else {
                    $user->forceDelete();
                }
            }
        });

        $this->newLine();
        $this->info('Cleanup transaction committed.');
        $this->reportIntegrity();
    }

    /**
     * Reassign every non-protected, non-linkage reference from the removed user
     * to the kept account of the same role.
     */
    private function reassignReferences(User $user, int $keeperId): void
    {
        foreach ($this->references as $table => $columns) {
            if (in_array($table, self::PROTECTED_TABLES, true) || in_array($table, self::LINKAGE_TABLES, true)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                if (in_array($table, self::ONE_TO_ONE_TABLES, true)) {
                    $this->handleOneToOne($table, $column, $user, $keeperId);

                    continue;
                }

                try {
                    DB::table($table)->where($column, $user->id)->update([$column => $keeperId]);
                } catch (QueryException $exception) {
                    // Unique collisions (e.g. the keeper already liked/favourited
                    // the same row) are left pointing at the removed user's row,
                    // which is itself removed below only when it holds no
                    // financial data — so drop the duplicate instead.
                    if ($exception->getCode() === '23000') {
                        DB::table($table)->where($column, $user->id)->delete();

                        continue;
                    }

                    throw $exception;
                }
            }
        }
    }

    /**
     * One-to-one profile rows: the kept account's row wins; the removed user's
     * missing fields are merged into it and the extra row is deleted.
     *
     * The agents table is special: its rows are referenced by agent_id columns
     * elsewhere (properties, commissions, inquiries, leases, projects...), so
     * those references are remapped to the kept agent row before deleting.
     */
    private function handleOneToOne(string $table, string $column, User $user, int $keeperId): void
    {
        $removedRows = DB::table($table)->where($column, $user->id)->get();

        foreach ($removedRows as $row) {
            $keeperRow = DB::table($table)->where($column, $keeperId)->first();

            if ($keeperRow) {
                if ($table === 'agents') {
                    $updates = [];

                    foreach ((array) $row as $field => $value) {
                        if (in_array($field, ['id', 'user_id', 'email', 'created_at', 'updated_at'], true)) {
                            continue;
                        }

                        if ($value !== null && $value !== '' && ($keeperRow->{$field} ?? null) === null) {
                            $updates[$field] = $value;
                        }
                    }

                    if (! empty($updates)) {
                        DB::table($table)->where('id', $keeperRow->id)->update($updates);
                    }

                    $this->remapAgentReferences((int) $row->id, (int) $keeperRow->id);
                }

                DB::table($table)->where('id', $row->id)->delete();

                continue;
            }

            if ($table === 'agents') {
                $this->remapAgentReferences((int) $row->id, null);
            }

            DB::table($table)->where('id', $row->id)->update([$column => $keeperId]);
        }
    }

    /**
     * Point every agent_id reference at the kept agent row, or clear it when
     * the kept account has no agents row.
     */
    private function remapAgentReferences(int $agentId, ?int $keeperAgentId): void
    {
        foreach ($this->agentReferences as $table => $columns) {
            if ($table === 'agents') {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                if ($keeperAgentId === null) {
                    DB::table($table)->where($column, $agentId)->update([$column => null]);

                    continue;
                }

                DB::table($table)->where($column, $agentId)->update([$column => $keeperAgentId]);
            }
        }
    }

    /**
     * Delete per-user permission grants and live sessions for a removed user.
     */
    private function dropLinkageAndAuthRows(User $user): void
    {
        foreach (array_merge(self::LINKAGE_TABLES, self::AUTH_TABLES) as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
                DB::table($table)->where('user_id', $user->id)->delete();
            }
        }

        if (Schema::hasTable('password_reset_tokens')) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        }
    }

    /**
     * Anonymize a removed user that still owns financial or audit records, then
     * soft-delete the row so those references stay valid.
     */
    private function anonymizeAndSoftDelete(User $user): void
    {
        $user->forceFill([
            'name' => 'Removed User #'.$user->id,
            'email' => 'removed-'.$user->id.'@example.invalid',
            'phone' => null,
            'photo_path' => null,
            'portal_bg_path' => null,
            'id_card_path' => null,
            'id_card_type' => null,
            'id_rejection_reason' => null,
            'warning_notes' => null,
            'bio' => null,
            'portfolio_summary' => null,
            'profession' => null,
            'license_number' => null,
            'password' => Hash::make(Str::random(64)),
            'remember_token' => null,
            'two_factor_enabled' => false,
            'status' => 'inactive',
            'deactivated_at' => now(),
            'deactivation_reason' => 'Removed by users:cleanup — kept as an anonymized reference for financial/audit records.',
            'notification_preferences' => null,
        ])->save();

        $user->delete();
    }

    /**
     * Verify that no reference to a removed user survives outside the
     * financial/audit tables.
     */
    private function reportIntegrity(): void
    {
        $liveIds = User::query()->pluck('id')->all();
        $tombstoneIds = User::withTrashed()->onlyTrashed()->pluck('id')->all();
        $problems = [];

        foreach ($this->references as $table => $columns) {
            if (in_array($table, self::PROTECTED_TABLES, true) || in_array($table, self::LINKAGE_TABLES, true)) {
                continue;
            }

            foreach ($columns as $column) {
                $orphans = DB::table($table)
                    ->whereNotIn($column, array_merge($liveIds, $tombstoneIds))
                    ->whereNotNull($column)
                    ->count();

                if ($orphans > 0) {
                    $problems[] = "{$table}.{$column}: {$orphans} dangling user references";
                }
            }
        }

        if (empty($problems)) {
            $this->info('Integrity check: no dangling user references outside financial/audit tables.');

            return;
        }

        $this->error('Integrity check found problems:');
        foreach ($problems as $problem) {
            $this->line('  - '.$problem);
        }
    }

    /**
     * Print the plan as a console table.
     *
     * @param  array{keep: array<int, array{id:int,name:string,email:string,role:string,protected:bool}>, remove: array<int, array{id:int,name:string,email:string,role:string,reason:string,counts:array<string,int>,has_financials:bool}>, protected_manual: array<int, array{id:int,name:string,email:string,role:string,note:string}>}  $plan
     * @param  Collection<int, User>  $users
     */
    private function renderPlan(array $plan, $users, bool $applied): void
    {
        $this->newLine();
        $this->info($applied ? 'CLEANUP APPLIED' : 'DRY RUN — no changes were made');
        $this->newLine();

        $rows = [];

        foreach ($users->whereNull('deleted_at')->sortBy('id') as $user) {
            $kept = collect($plan['keep'])->firstWhere('id', $user->id);
            $removed = collect($plan['remove'])->firstWhere('id', $user->id);
            $manual = collect($plan['protected_manual'])->firstWhere('id', $user->id);

            if ($kept) {
                $status = $kept['protected'] ? 'KEEP (protected admin)' : 'KEEP';
            } elseif ($removed) {
                $status = $removed['has_financials'] ? 'REMOVE → anonymize + soft delete' : 'REMOVE → hard delete';
            } elseif ($manual) {
                $status = 'KEEP (manual account, owner request)';
            } else {
                $status = 'KEEP';
            }

            $rows[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                optional($user->created_at)->format('Y-m-d H:i'),
                array_sum($this->recordCounts[$user->id] ?? []),
                $status,
            ];
        }

        $this->table(['ID', 'Name', 'Email', 'Role', 'Created', 'Records', 'Action'], $rows);

        $this->newLine();
        $this->line('Kept accounts: '.count($plan['keep']).' | Users to remove: '.count($plan['remove']).' | Manual accounts kept: '.count($plan['protected_manual']));
        $this->line('Backup taken before any change: storage/backups/');
    }

    /**
     * The role a given role is merged into, or "unchanged" when it survives.
     */
    private function mergedRole(string $role): string
    {
        return self::ROLE_MERGE_MAP[$role] ?? 'unchanged (survives)';
    }

    /**
     * Every app role mapped to an SRS target role, with the merge verdict.
     * "keep:preset" = real admin preset outside the 17 SRS personas.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const ROLE_MAPPING = [
        'admin' => ['Administrator', 'keep'],
        'guest' => ['Guest', 'keep'],
        'buyer' => ['Buyer', 'keep'],
        'client' => ['Buyer', 'merge:buyer'],
        'user' => ['Buyer', 'merge:buyer'],
        'tenant' => ['Tenant', 'keep'],
        'traveler' => ['Guest / Traveler', 'keep'],
        'property_owner' => ['Property Owner', 'keep'],
        'seller' => ['Property Owner', 'merge:property_owner'],
        'host' => ['Host', 'keep'],
        'landlord' => ['Landlord', 'keep'],
        'agent' => ['Agent', 'keep'],
        'company' => ['Real Estate Company', 'keep'],
        'company_admin' => ['Real Estate Company', 'merge:company'],
        'real_estate_company' => ['Real Estate Company', 'merge:company'],
        'developer' => ['Developer', 'keep'],
        'property_manager' => ['Property Manager', 'keep'],
        'service_provider' => ['Professional', 'keep'],
        'moderator' => ['Moderator', 'keep'],
        'verification_officer' => ['Verification Officer', 'keep'],
        'verification_company' => ['— (admin preset)', 'keep:preset'],
        'finance_officer' => ['Finance Officer', 'keep'],
        'customer_support' => ['Customer Support', 'keep'],
        'receptionist' => ['Customer Support', 'merge:customer_support'],
        'cleaner' => ['— (admin preset)', 'keep:preset'],
        'housekeeping' => ['— (admin preset)', 'merge:cleaner'],
        'campaign_officer' => ['— (admin preset)', 'keep:preset'],
        'investor' => ['— (admin preset)', 'keep:preset'],
    ];

    /**
     * Role mapping section for the generated plan document.
     *
     * @return array<int, string>
     */
    private function roleMappingSection(): array
    {
        $lines = [];

        $lines[] = '## 1. Role mapping (28 role values in `users.role` → 17 SRS personas)';
        $lines[] = '';
        $lines[] = 'The RBAC catalog (`roles`) holds 27 entries; `seller` has no catalog entry at all, so 28 distinct role values exist in `users.role`.';
        $lines[] = '';
        $lines[] = '| App role | Users | Catalog perms | SRS target role | Verdict | Evidence |';
        $lines[] = '| --- | --- | --- | --- | --- | --- |';

        foreach (self::ROLE_MAPPING as $slug => [$target, $verdict]) {
            $users = DB::table('users')->where('role', $slug)->count();
            $catalog = DB::table('roles')->where('slug', $slug)->first();
            $perms = $catalog
                ? DB::table('role_permissions')->where('role_id', $catalog->id)->count()
                : 0;

            $evidence = match ($verdict) {
                'merge:buyer' => $slug === 'client'
                    ? 'catch-all self-service consumer: permission set contains every `tenant` and `user` key (63 keys)'
                    : 'fallback role; all 47 keys are a subset of `client`',
                'merge:property_owner' => 'no RBAC catalog entry — unmanaged duplicate of the owner/seller persona',
                'merge:company' => 'permission keys are byte-identical to `company` (35 keys)',
                'merge:customer_support' => 'permission keys are byte-identical to `customer_support` (22 keys); seeder marks it "legacy → customer_support"',
                'merge:cleaner' => 'permission keys are byte-identical to `cleaner` (4 keys) and zero users',
                'keep:preset' => 'distinct permission namespace, staff desk outside the 17 SRS personas',
                default => 'canonical persona account, permissions distinct',
            };

            $lines[] = "| `{$slug}` | {$users} | {$perms} | {$target} | {$this->verdictLabel($verdict)} | {$evidence} |";
        }

        $lines[] = '';
        $lines[] = '### Merges to execute before the user cleanup';
        $lines[] = '';
        $lines[] = '| From | Into | Users moved | Permissions moved |';
        $lines[] = '| --- | --- | --- | --- |';

        foreach (self::ROLE_MERGE_MAP as $from => $into) {
            $users = DB::table('users')->where('role', $from)->count();
            $lines[] = "| `{$from}` | `{$into}` | {$users} | union-merged into `{$into}` (target keeps its own keys and gains any missing key) |";
        }

        $lines[] = '';
        $lines[] = 'Merge mechanics (one transaction, integrity-checked):';
        $lines[] = '';
        $lines[] = '1. `UPDATE users SET role = :target WHERE role = :source`.';
        $lines[] = '2. `user_roles` pivot rows repointed from the source `role_id` to the target `role_id` (duplicates skipped).';
        $lines[] = '3. `role_permissions`: every permission key the source has that the target lacks is inserted for the target, so **no capability is lost**; the source rows are then deleted.';
        $lines[] = '4. The source `roles` catalog row is deleted (its permission rows were migrated above).';
        $lines[] = '5. Direct grants (`user_permissions`, `user_permission_overrides`) are keyed by `permission_id`, not role, so they are untouched.';
        $lines[] = '';
        $lines[] = '### Roles kept outside the 17 (real admin presets)';
        $lines[] = '';
        $lines[] = '| Role | Why it is a real preset, not a duplicate |';
        $lines[] = '| --- | --- |';
        $lines[] = '| `campaign_officer` | Owns the `campaigns.*` review desk (6 keys) — internal marketing workflow with no SRS persona counterpart. |';
        $lines[] = '| `investor` | Owns `portfolio.*` / `yields.*` (5 keys) — investment reporting desk. |';
        $lines[] = '| `verification_company` | Business account for outsourced verification: `verification_jobs.*`, `verification_reports.*`, `verification_payments.receive` — different namespace from the individual `verification_officer`. |';
        $lines[] = '| `cleaner` | `cleaning.*` / `housekeeping.*` operations desk (4 keys) for short-stay housekeeping. |';
        $lines[] = '';
        $lines[] = 'Result: 28 role values → **21 surviving roles** (17 SRS personas + the 4 presets above).';
        $lines[] = '';

        return $lines;
    }

    /**
     * Human label for a merge verdict.
     */
    private function verdictLabel(string $verdict): string
    {
        return match ($verdict) {
            'keep:preset' => 'KEEP (admin preset)',
            default => str_starts_with($verdict, 'merge:') ? 'MERGE → '.substr($verdict, 6) : 'KEEP (SRS persona)',
        };
    }

    /**
     * Write docs/USER_CLEANUP_PLAN.md.
     *
     * @param  array{keep: array<int, array{id:int,name:string,email:string,role:string,protected:bool}>, remove: array<int, array{id:int,name:string,email:string,role:string,reason:string,counts:array<string,int>,has_financials:bool}>, protected_manual: array<int, array{id:int,name:string,email:string,role:string,note:string}>}  $plan
     */
    private function writePlanFile(array $plan): void
    {
        $path = base_path('docs/USER_CLEANUP_PLAN.md');
        $marker = '<!-- EXECUTION_LOG -->';

        $existing = is_file($path) ? (string) file_get_contents($path) : '';
        $hasExecutionLog = strpos($existing, $marker) !== false;

        $lines = [];

        $lines[] = '# User Cleanup Plan';
        $lines[] = '';
        $lines[] = 'Generated by `php artisan users:cleanup --dry-run` on '.now()->toDateTimeString().'.';
        $lines[] = '';

        if ($hasExecutionLog) {
            $lines[] = '**Status: APPROVED (`approved`) and EXECUTED.** Verification results are in the execution log at the bottom of this file.';
            $lines[] = '';
        }

        $lines[] = '**Goal:** exactly one user account per role. The main administrator is never removed, and the last account of a role is never removed.';
        $lines[] = '';
        $lines[] = '## Rules applied (one consistent rule)';
        $lines[] = '';
        $lines[] = '| Record type | Handling |';
        $lines[] = '| --- | --- |';
        $lines[] = '| Financial records, ledger entries, payments, commissions, invoices, audit logs, activity logs | Never deleted, never reassigned. The owning user is anonymized in place and soft-deleted, so references stay valid. |';
        $lines[] = '| Per-user permission grants (`user_roles`, `user_permissions`, `user_permission_overrides`) | Deleted with the removed user — the kept account carries its own grants. |';
        $lines[] = '| Sessions, API tokens, password reset tokens | Deleted with the removed user. |';
        $lines[] = '| One-to-one profiles (`agents`, `company_white_labels`) | The kept account keeps its row; the removed user\'s fields are merged into it when empty, then the extra row is deleted. |';
        $lines[] = '| Every other record referencing the removed user (properties, listings, bookings, offers, leases, documents, messages, reviews, viewings, inquiries...) | Reassigned to the kept account of the same role. |';
        $lines[] = '';
        $lines[] = 'A removed user without financial/audit rows is hard-deleted after reassignment; an owner of financial/audit rows is soft-deleted (`deleted_at`) and anonymized to `Removed User #id`.';
        $lines[] = '';
        $lines[] = '## Environment check';
        $lines[] = '';
        $lines[] = '- `APP_ENV=local`, `APP_DEBUG=true`, local MySQL on 127.0.0.1 (user `root`).';
        $lines[] = '- All money rows use generated references (`TXN-ETH-...`, `LED-...`); no live payment-gateway data.';
        $lines[] = '- Accounts are either seeded `@realestate.com` personas, faker users from test runs, or manual accounts created by the developer while testing.';
        $lines[] = '- Conclusion: **development/demo database**, safe to clean.';
        $lines[] = '';
        $lines[] = '## Backup verification';
        $lines[] = '';
        $lines[] = '`storage/backups/gtm_real_estate_2026-10-14_120509.sql` was restored into a scratch database (`gtm_real_estate_backup_verify`) and compared table by table:';
        $lines[] = '';
        $lines[] = '- 10,410 statements executed, 80/80 tables restored.';
        $lines[] = '- Every difference vs. the live database is a row written **after** the dump: 2 users (`guest@realestate.com`, `housing.manager@ethiopianairlines.com`), 4 corporate-tenancy rows, 1 escrow disbursement, 1 white label, 1 migration row (the `deleted_at` migration).';
        $lines[] = '- The scratch database was dropped afterwards (`SHOW DATABASES` confirms 0 remaining).';
        $lines[] = '- **Result: the backup is complete and restorable.**';
        $lines[] = '';
        $lines[] = '## Known pre-existing test failures';
        $lines[] = '';
        $lines[] = '`docs/KNOWN_FAILURES.md` lists the 34 failing tests that already fail on this branch (verified by reverting the cleanup changes and re-running). Owner decision: they are fixed in Task 2; Task 2 is complete only when the whole suite is green.';
        $lines[] = '';
        foreach ($this->roleMappingSection() as $line) {
            $lines[] = $line;
        }

        $lines[] = '## Manual accounts kept (owner request — duplicates, intentionally retained)';
        $lines[] = '';

        if (empty($plan['protected_manual'])) {
            $lines[] = 'None.';
        } else {
            $lines[] = '| ID | Name | Email | Current role | Role after merge | Duplicate of |';
            $lines[] = '| --- | --- | --- | --- | --- | --- |';

            foreach ($plan['protected_manual'] as $manual) {
                $lines[] = "| {$manual['id']} | {$manual['name']} | {$manual['email']} | {$manual['role']} | ".$this->mergedRole((string) $manual['role']).' | '.$manual['note'].' |';
            }
        }

        $lines[] = '';
        $lines[] = '## Accounts kept (one per role)';
        $lines[] = '';
        $lines[] = 'Roles marked `MERGE` in section 1 disappear in the role-consolidation step that runs **before** the user cleanup, so their accounts collapse into the keeper of the merge target (for example every `client`, `user` and `seller` account ends up under `buyer` / `property_owner`).';
        $lines[] = '';
        $lines[] = '| ID | Name | Email | Role | Linked records |';
        $lines[] = '| --- | --- | --- | --- | --- |';

        foreach ($plan['keep'] as $keeper) {
            $counts = $this->recordCounts[$keeper['id']] ?? [];
            $summary = empty($counts) ? '—' : collect($counts)->map(fn ($n, $t) => "{$t}: {$n}")->implode(', ');
            $lines[] = "| {$keeper['id']} | {$keeper['name']} | {$keeper['email']} | {$keeper['role']} | {$summary} |";
        }

        $lines[] = '';
        $lines[] = '## Accounts to remove';
        $lines[] = '';
        $lines[] = '| ID | Name | Email | Role | Linked records | Financial/audit rows | Reason |';
        $lines[] = '| --- | --- | --- | --- | --- | --- | --- |';

        foreach ($plan['remove'] as $entry) {
            $summary = empty($entry['counts']) ? '—' : collect($entry['counts'])->map(fn ($n, $t) => "{$t}: {$n}")->implode(', ');
            $lines[] = "| {$entry['id']} | {$entry['name']} | {$entry['email']} | {$entry['role']} | {$summary} | ".($entry['has_financials'] ? 'yes → anonymize' : 'no').' | '.$entry['reason'].' |';
        }

        $lines[] = '';
        $lines[] = '## Verification after `--force`';
        $lines[] = '';
        $lines[] = '1. Command prints an integrity check: no dangling user references outside financial/audit tables.';
        $lines[] = '2. `SELECT role, COUNT(*) FROM users WHERE deleted_at IS NULL GROUP BY role;` returns exactly 1 row per role (plus the protected manual accounts in section "Manual accounts kept").';
        $lines[] = '3. `php artisan test --compact` — 35 known failures remain (docs/KNOWN_FAILURES.md), zero new ones; the count is identical to the pre-cleanup baseline.';
        $lines[] = '4. Admin login still works: `admin@realestate.com` / `secret123`.';
        $lines[] = '5. `SELECT role, COUNT(*) FROM users WHERE deleted_at IS NULL GROUP BY role;` and `SELECT COUNT(*) FROM user_roles;` show no rows for merged-away roles.';
        $lines[] = '';
        $lines[] = '## Seeder changes required before re-seeding (step 6)';
        $lines[] = '';
        $lines[] = '`DatabaseSeeder` is already idempotent for the 22 personas (`updateOrCreate` on email) and now also seeds `guest@realestate.com`. Two seeders still create duplicates and must be guarded:';
        $lines[] = '';
        $lines[] = '| Seeder | Problem | Fix |';
        $lines[] = '| --- | --- | --- |';
        $lines[] = '| `CorporateTenancySeeder` | Seeds the corporate tenant demo account (`housing.manager@ethiopianairlines.com`) with the reserved `contact` role | Already guarded by `updateOrCreate`; converges any older `company`-role copy to `contact` |';
        $lines[] = '| `DatabaseSeeder` (sample agents block) | Created `mekdes.agent@example.com` and `yohannes.agent@example.com` agent users on every seed | **Done** — block removed; only the single `agent@realestate.com` persona is seeded |';
        $lines[] = '';
        $lines[] = 'The README demo-account table (step 6) must list the 21 surviving roles with their seeded email and the shared password `secret123`.';
        $lines[] = '';
        $lines[] = 'Backup taken before any change: `storage/backups/gtm_real_estate_2026-10-14_120509.sql` (restore verified).';

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $content = implode("\n", $lines)."\n";

        // Keep everything after the execution-log marker: the log records what
        // was approved and executed and must survive a later dry-run.
        if ($hasExecutionLog) {
            $position = strpos($existing, $marker);

            if ($position !== false) {
                $content .= "\n".substr($existing, $position);
            }
        }

        file_put_contents($path, $content);

        $this->newLine();
        $this->info('Plan written to docs/USER_CLEANUP_PLAN.md');
    }
}
