<?php

namespace App\Console\Commands;

use App\Models\FinanceRate;
use App\Models\Permission;
use App\Support\MigrationStatus;
use Database\Seeders\FinanceRateSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * One command that answers "is this environment actually ready to run?".
 *
 * It checks the things that silently break a real (MySQL) database while the
 * in-memory test suite stays green: a dead connection, migrations that were
 * never run, permission keys added to config but never seeded, finance rates
 * that were never seeded, and a mislabelled environment.
 */
#[Signature('app:preflight
            {--json : Emit the checks as JSON instead of a table}')]
#[Description('Check database connection, pending migrations, permission keys, seeded rates and APP_ENV')]
class PreflightCommand extends Command
{
    public function handle(): int
    {
        /** @var list<array{check:string,status:string,detail:string}> $results */
        $results = [
            $this->checkDatabase(),
            $this->checkMigrations(),
            $this->checkPermissions(),
            $this->checkRates(),
            $this->checkEnvironment(),
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($results, JSON_PRETTY_PRINT));
        } else {
            $this->table(
                ['check', 'status', 'detail'],
                array_map(fn (array $r): array => [$r['check'], $r['status'], $r['detail']], $results)
            );
        }

        $failed = array_filter($results, fn (array $r): bool => $r['status'] === 'FAIL');

        if ($failed !== []) {
            $this->newLine();
            $this->error(count($failed).' preflight check(s) failed.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Preflight OK.');

        return self::SUCCESS;
    }

    /**
     * @return array{check:string,status:string,detail:string}
     */
    private function checkDatabase(): array
    {
        try {
            $connection = DB::connection();
            $connection->getPdo();
            $connection->select('select 1');

            return $this->result('database', 'OK', sprintf('%s (%s)', $connection->getDatabaseName(), $connection->getDriverName()));
        } catch (Throwable $e) {
            return $this->result('database', 'FAIL', 'Cannot connect: '.$e->getMessage());
        }
    }

    /**
     * @return array{check:string,status:string,detail:string}
     */
    private function checkMigrations(): array
    {
        $pending = MigrationStatus::pending();

        if ($pending === []) {
            return $this->result('migrations', 'OK', 'No pending migrations.');
        }

        return $this->result(
            'migrations',
            'FAIL',
            count($pending).' pending: '.implode(', ', array_slice($pending, 0, 5)).(count($pending) > 5 ? ' …' : '')
        );
    }

    /**
     * @return array{check:string,status:string,detail:string}
     */
    private function checkPermissions(): array
    {
        $expected = collect(config('personas.personas', []))
            ->flatMap(fn (array $persona): array => array_keys($persona['permissions'] ?? []))
            ->merge(array_keys(config('personas.catalogue', [])))
            ->unique()
            ->values();

        try {
            $missing = $expected->diff(Permission::query()->pluck('key'))->values();
        } catch (Throwable $e) {
            return $this->result('permissions', 'FAIL', 'Cannot read catalogue: '.$e->getMessage());
        }

        if ($missing->isEmpty()) {
            return $this->result('permissions', 'OK', $expected->count().' catalogue keys present.');
        }

        return $this->result(
            'permissions',
            'FAIL',
            $missing->count().' missing (run permissions:sync): '.implode(', ', $missing->take(5)->all()).($missing->count() > 5 ? ' …' : '')
        );
    }

    /**
     * @return array{check:string,status:string,detail:string}
     */
    private function checkRates(): array
    {
        try {
            $missing = collect(FinanceRateSeeder::BASELINE)
                ->keys()
                ->filter(fn (string $key): bool => ! FinanceRate::query()
                    ->where('key', $key)
                    ->where('status', FinanceRate::STATUS_APPROVED)
                    ->exists())
                ->values();
        } catch (Throwable $e) {
            return $this->result('finance_rates', 'FAIL', 'Cannot read rates: '.$e->getMessage());
        }

        if ($missing->isEmpty()) {
            return $this->result('finance_rates', 'OK', 'Baseline rates seeded.');
        }

        // The money paths fall back to shipped defaults, so this is a warning,
        // not a failure: the rate book is simply not explicit yet.
        return $this->result(
            'finance_rates',
            'WARN',
            'No approved row for: '.implode(', ', $missing->all()).' (run db:seed --class=FinanceRateSeeder)'
        );
    }

    /**
     * @return array{check:string,status:string,detail:string}
     */
    private function checkEnvironment(): array
    {
        $env = (string) app()->environment();
        $example = base_path('.env.example');
        $exampleIsLocal = is_file($example)
            && (bool) preg_match('/^\s*APP_ENV\s*=\s*local\s*$/mi', (string) file_get_contents($example));

        if ($exampleIsLocal) {
            return $this->result('app_env', 'FAIL', '.env.example sets APP_ENV=local; the shipped default must not be a dev label.');
        }

        if ($env === 'local') {
            return $this->result('app_env', 'WARN', 'APP_ENV=local (fine for a dev machine, never for production).');
        }

        return $this->result('app_env', 'OK', 'APP_ENV='.$env.'.');
    }

    /**
     * @return array{check:string,status:string,detail:string}
     */
    private function result(string $check, string $status, string $detail): array
    {
        return ['check' => $check, 'status' => $status, 'detail' => $detail];
    }
}
