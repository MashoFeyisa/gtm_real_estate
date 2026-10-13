<?php

use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->financeOfficer = User::create([
        'name' => 'Finance Fikirte',
        'email' => 'fikirte.finance@example.com',
        'password' => Hash::make('password123'),
        'role' => 'finance_officer',
    ]);
});

test('finance officer dashboard shows every granted desk section', function () {
    $this->seed(RolePermissionSeeder::class);

    $response = $this->actingAs($this->financeOfficer)->get(route('finance.index'));

    $response->assertOk();
    $response->assertSee('Payments to Confirm');
    $response->assertSee('Receive Commission');
    $response->assertSee('Double-Entry Ledgers');
    $response->assertSee('Finance Officer');
    $response->assertDontSee('Your Capabilities');
});

test('finance dashboard hides a desk the officer may not use', function () {
    $this->seed(RolePermissionSeeder::class);

    UserPermissionOverride::create([
        'user_id' => $this->financeOfficer->id,
        'permission_id' => Permission::query()->where('key', 'commissions.manage')->value('id'),
        'granted' => false,
    ]);

    $response = $this->actingAs($this->financeOfficer)->get(route('finance.index'));

    $response->assertOk();
    $response->assertDontSee('Receive Commission');
    $response->assertSee('Payments to Confirm');
});

test('revoking a desk permission hides its finance navigation and section', function () {
    $this->seed(RolePermissionSeeder::class);

    UserPermissionOverride::create([
        'user_id' => $this->financeOfficer->id,
        'permission_id' => Permission::query()->where('key', 'payments.manage')->value('id'),
        'granted' => false,
    ]);

    $response = $this->actingAs($this->financeOfficer)->get(route('finance.index'));

    $response->assertOk();
    $response->assertDontSee('Payments to Confirm');
    $response->assertDontSee('Payments Awaiting Confirmation');
    $response->assertDontSee('Manage payments');
    $response->assertSee('Double-Entry Financial Ledgers');
});

test('admins keep full access to the finance desk on a seeded catalog', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::create([
        'name' => 'Platform Admin',
        'email' => 'admin.finance@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get(route('finance.index'));

    $response->assertOk();
    $response->assertSee('Payments to Confirm');
    $response->assertSee('Administrator');
    $response->assertSee('Double-Entry Financial Ledgers');
});
