<?php

use App\Models\User;
use App\Services\LedgerService;
use App\Services\PayoutService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * A true two-process payout race, run against MySQL/MariaDB.
 *
 * In-memory SQLite runs in one process and has no real row locks, so a
 * sequential test can only prove the arithmetic. This test forks two OS
 * processes that both call `PayoutService::request()` at the same instant and
 * proves the `lockForUpdate()` on the party row serialises them: exactly one
 * payout is created and the other is rejected for exceeding the balance.
 */
test('two simultaneous payout processes on mysql leave exactly one payout', function () {
    if (DB::connection()->getDriverName() !== 'mysql') {
        $this->markTestSkipped('Real row locks require MySQL/MariaDB, not in-memory SQLite.');
    }

    if (! function_exists('pcntl_fork')) {
        $this->markTestSkipped('pcntl is required to fork the concurrent payout processes.');
    }

    $party = User::create([
        'name' => 'Race Agent',
        'email' => 'race.'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    // A positive, ledger-derived balance of 1,000 ETB.
    app(LedgerService::class)->post('commission:mysql-race-'.uniqid(), [
        ['entry_type' => 'debit', 'category' => 'agent_commission', 'amount' => 1000, 'currency' => 'ETB', 'description' => 'Earned', 'user_id' => $party->id, 'party_user_id' => $party->id],
        ['entry_type' => 'credit', 'category' => 'agent_payable', 'amount' => 1000, 'currency' => 'ETB', 'description' => 'Payable', 'user_id' => $party->id, 'party_user_id' => $party->id],
    ]);

    // Commit so the forked children can see the party and its ledger. This also
    // means RefreshDatabase can no longer roll the rows back, so the test
    // deletes them explicitly below.
    DB::commit();

    $dir = storage_path('app/payout-race-'.uniqid());
    mkdir($dir, 0777, true);
    $barrier = $dir.'/go';

    $children = [];

    foreach ([0, 1] as $index) {
        $pid = pcntl_fork();

        if ($pid === -1) {
            $this->fail('Could not fork a payout process.');
        }

        if ($pid === 0) {
            // Wait until both children are ready so the two requests overlap.
            while (! file_exists($barrier)) {
                usleep(1000);
            }

            $result = 'fail';

            try {
                // Never share the parent's PDO socket: use a fresh connection.
                config(['database.connections.mysql_race' => config('database.connections.mysql')]);
                config(['database.default' => 'mysql_race']);
                DB::purge('mysql_race');

                app(PayoutService::class)->request($party, 800, 'telebirr', null, null, 'agent');
                $result = 'ok';
            } catch (Throwable) {
                $result = 'fail';
            }

            file_put_contents($dir.'/'.$index, $result);

            // Exit without running destructors so the inherited connection is
            // never closed underneath the parent process.
            posix_kill(posix_getpid(), SIGKILL);
            exit(0);
        }

        $children[] = $pid;
    }

    file_put_contents($barrier, 'go');

    foreach ($children as $pid) {
        pcntl_waitpid($pid, $status);
    }

    $results = [];
    foreach ([0, 1] as $index) {
        $results[] = trim((string) @file_get_contents($dir.'/'.$index));
    }

    // Remove the committed rows.
    DB::table('notifications')->where('notifiable_type', User::class)->where('notifiable_id', $party->id)->delete();
    DB::table('audit_logs')->where('user_id', $party->id)->delete();
    DB::table('payout_requests')->where('user_id', $party->id)->delete();
    DB::table('financial_ledgers')->where('user_id', $party->id)->orWhere('party_user_id', $party->id)->delete();
    DB::table('users')->where('id', $party->id)->delete();

    @unlink($barrier);
    @unlink($dir.'/0');
    @unlink($dir.'/1');
    @rmdir($dir);

    expect($results)->toHaveCount(2)
        ->and(array_filter($results, fn (string $r): bool => $r === 'ok'))->toHaveCount(1);
});
