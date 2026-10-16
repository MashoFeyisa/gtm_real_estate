<?php

use App\Models\Agent;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

/**
 * The canonical demo accounts (<key>@realestate.com, one per persona) carry an
 * explicit is_demo flag so tooling can separate seeded demo data from manually
 * created accounts without relying on the email domain alone.
 */
test('demo:user-accounts --sync flags only the canonical persona accounts', function () {
    $this->seed(DatabaseSeeder::class);

    $manual = User::factory()->create(['email' => 'manual.dup@example.com', 'role' => 'buyer']);
    $manual->forceFill(['is_demo' => true, 'demo_role_key' => 'buyer'])->save();

    $this->artisan('demo:user-accounts', ['--sync' => true])->assertExitCode(0);

    $expectedEmails = User::query()
        ->whereNull('deleted_at')
        ->where('email', 'like', '%@realestate.com')
        ->pluck('email')
        ->sort()
        ->values();

    $flaggedEmails = User::query()->where('is_demo', true)->pluck('email')->sort()->values();

    expect($expectedEmails)->not->toBeEmpty()
        ->and($flaggedEmails->all())->toEqual($expectedEmails->all())
        ->and(User::query()->find($manual->id)->is_demo)->toBeFalse();
});

test('demo:user-accounts --repair restores drifted demo credentials and activation', function () {
    $this->seed(DatabaseSeeder::class);

    $agent = User::query()->where('email', 'agent@realestate.com')->firstOrFail();
    $agent->forceFill(['password' => Hash::make('old-drifted-password'), 'email_verified_at' => null])->save();
    Agent::query()->where('user_id', $agent->id)->update(['is_active' => false]);

    $this->artisan('demo:user-accounts', ['--repair' => true])->assertExitCode(0);

    $agent->refresh();

    expect(Hash::check('secret123', (string) $agent->password))->toBeTrue()
        ->and($agent->email_verified_at)->not->toBeNull()
        ->and($agent->is_demo)->toBeTrue()
        ->and(Agent::query()->where('user_id', $agent->id)->value('is_active'))->toBeTruthy();
});
