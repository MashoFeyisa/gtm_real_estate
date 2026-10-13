<?php

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

/*
|--------------------------------------------------------------------------
| 1. Custom Permissions & Admin Role Management
|--------------------------------------------------------------------------
*/

test('admin can create custom resource.action permissions stored in database with audit logging', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('dashboard.permissions.store'), [
        'key' => 'payment.refund',
        'label' => 'Refund Payments',
        'group' => 'payments',
        'description' => 'Grant capability to issue transaction refunds',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $permission = Permission::where('key', 'payment.refund')->first();
    expect($permission)->not()->toBeNull();
    expect($permission->group)->toBe('payments');
    expect($permission->label)->toBe('Refund Payments');

    // Confirm audit log recorded the permission creation
    $audit = AuditLog::where('action', 'permission.created')
        ->where('entity_id', $permission->id)
        ->first();

    expect($audit)->not()->toBeNull();
    expect($audit->user_id)->toBe($admin->id);
    expect($audit->new_value['key'])->toBe('payment.refund');
});

test('admin can create, edit, and delete custom roles with audit logging', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // 1. Create Role
    $createResponse = $this->actingAs($admin)->post(route('dashboard.roles.store'), [
        'name' => 'Compliance Officer',
        'description' => 'Legal and compliance review specialist',
    ]);
    $createResponse->assertRedirect();

    $role = Role::where('name', 'Compliance Officer')->first();
    expect($role)->not()->toBeNull();
    expect($role->slug)->toBe('compliance_officer');

    $createAudit = AuditLog::where('action', 'role.created')
        ->where('entity_id', $role->id)
        ->first();
    expect($createAudit)->not()->toBeNull();

    // 2. Edit Role
    $editResponse = $this->actingAs($admin)->put(route('dashboard.roles.update', $role), [
        'name' => 'Senior Compliance Officer',
        'description' => 'Senior legal specialist',
    ]);
    $editResponse->assertRedirect();

    $role->refresh();
    expect($role->name)->toBe('Senior Compliance Officer');

    $updateAudit = AuditLog::where('action', 'role.updated')
        ->where('entity_id', $role->id)
        ->first();
    expect($updateAudit)->not()->toBeNull();

    // 3. Delete Role
    $deleteResponse = $this->actingAs($admin)->delete(route('dashboard.roles.destroy', $role));
    $deleteResponse->assertRedirect();

    expect(Role::where('id', $role->id)->exists())->toBeFalse();

    $deleteAudit = AuditLog::where('action', 'role.deleted')
        ->where('entity_id', $role->id)
        ->first();
    expect($deleteAudit)->not()->toBeNull();
});

test('user-level permission override takes precedence over role defaults', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    // Buyer role by default has offers.create
    expect($buyer->hasPermission('offers.create'))->toBeTrue();

    // Admin sets UserPermissionOverride to FALSE for this specific buyer
    $offerPerm = Permission::where('key', 'offers.create')->first();
    UserPermissionOverride::create([
        'user_id' => $buyer->id,
        'permission_id' => $offerPerm->id,
        'granted' => false,
    ]);

    // User override beats the role grant immediately
    expect($buyer->fresh()->hasPermission('offers.create'))->toBeFalse();

    // Now grant an extra unassigned permission (e.g. platform.roles_manage)
    $rolesPerm = Permission::where('key', 'platform.roles_manage')->first();
    UserPermissionOverride::create([
        'user_id' => $buyer->id,
        'permission_id' => $rolesPerm->id,
        'granted' => true,
    ]);

    expect($buyer->fresh()->hasPermission('platform.roles_manage'))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| 2. Sidebar by Permission & Immediate Revocation Enforcement
|--------------------------------------------------------------------------
*/

test('sidebar displays only permitted menu items and item disappears immediately when permission is revoked', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    // Initially buyer has offers and saved searches permissions
    $response = $this->actingAs($buyer)->get(route('buyer.dashboard'));
    $response->assertOk();
    $response->assertSee('data-target-section="offers"', false);
    $response->assertSee('data-target-section="saved-searches"', false);

    // Revoke offers permissions from this buyer
    $offerPermIds = Permission::whereIn('key', ['offers.create', 'offers.view', 'offer.create', 'offer.view'])->pluck('id');
    foreach ($offerPermIds as $pId) {
        UserPermissionOverride::create([
            'user_id' => $buyer->id,
            'permission_id' => $pId,
            'granted' => false,
        ]);
    }

    // Refresh and visit buyer dashboard again
    $updatedResponse = $this->actingAs($buyer->fresh())->get(route('buyer.dashboard'));
    $updatedResponse->assertOk();
    $updatedResponse->assertDontSee('data-target-section="offers"', false);
    $updatedResponse->assertSee('data-target-section="saved-searches"', false);
});

test('revoking property creation permission blocks seller from creating properties with 403', function () {
    $seller = User::factory()->create(['role' => 'seller']);

    // Initially seller has properties.create
    expect($seller->can('create', Property::class))->toBeTrue();

    // Revoke properties.create from this seller
    $createPermId = Permission::where('key', 'properties.create')->value('id');
    UserPermissionOverride::create([
        'user_id' => $seller->id,
        'permission_id' => $createPermId,
        'granted' => false,
    ]);

    expect($seller->fresh()->can('create', Property::class))->toBeFalse();

    // Visiting seller dashboard does not show properties sidebar item when property permissions are revoked
    $propPermIds = Permission::whereIn('key', ['properties.create', 'properties.update', 'properties.view', 'properties.delete'])->pluck('id');
    foreach ($propPermIds as $pId) {
        UserPermissionOverride::updateOrCreate([
            'user_id' => $seller->id,
            'permission_id' => $pId,
        ], ['granted' => false]);
    }

    $response = $this->actingAs($seller->fresh())->get(route('seller.dashboard'));
    $response->assertOk();
    $response->assertDontSee('data-target-section="properties"', false);

    // Attempting to store listing directly returns 403 Forbidden
    $storeResponse = $this->actingAs($seller->fresh())->post(route('seller.listings.store'), [
        'title' => 'Bole Penthouse Test',
        'type' => 'sale',
        'price' => 5000000,
        'city' => 'Addis Ababa',
    ]);
    $storeResponse->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| 3. Guest Role, Public Browsing, and Registration Prompts
|--------------------------------------------------------------------------
*/

test('guest can browse listings and search short stays without login', function () {
    $property = Property::factory()->create([
        'title' => 'Bole Road Modern Apartment',
        'slug' => 'bole-road-modern-apartment-guest-test',
        'type' => 'sale',
        'is_active' => true,
        'status' => 'published',
    ]);

    // Guest can browse property index
    $indexResponse = $this->get(route('properties.index'));
    $indexResponse->assertOk();

    // Guest can view property detail
    $showResponse = $this->get(route('properties.show', $property->slug));
    $showResponse->assertOk();

    // Guest can search short stays
    $staysResponse = $this->get(route('stays.index'));
    $staysResponse->assertOk();
});

test('guest is prompted to register or login when attempting booking, messaging, saving, or making offers', function () {
    $property = Property::factory()->create([
        'title' => 'Kazanchis Executive Suite',
        'slug' => 'kazanchis-executive-suite-prompt-test',
        'type' => 'stay',
        'is_active' => true,
        'status' => 'published',
    ]);

    // 1. Attempting to book a stay
    $bookResponse = $this->post(route('stays.book', $property), [
        'guest_name' => 'Unauthenticated Traveler',
        'guest_email' => 'guest@example.com',
        'guest_phone' => '+251911000222',
        'check_in_date' => now()->addDays(2)->format('Y-m-d'),
        'check_out_date' => now()->addDays(5)->format('Y-m-d'),
    ]);
    $bookResponse->assertRedirect(route('register'));
    $bookResponse->assertSessionHas('signup_prompt', function ($prompt) {
        return str_contains($prompt, 'Register / Login to continue');
    });

    // 2. Attempting to submit formal offer
    $offerResponse = $this->post(route('properties.offers.store', $property), [
        'offer_amount' => 15000000,
        'buyer_name' => 'Unauthenticated Buyer',
        'buyer_email' => 'buyer.guest@example.com',
    ]);
    $offerResponse->assertRedirect(route('register'));
    $offerResponse->assertSessionHas('signup_prompt', function ($prompt) {
        return str_contains($prompt, 'Register / Login to continue');
    });

    // 3. Attempting to send message / inquiry
    $contactResponse = $this->post(route('contact.store'), [
        'name' => 'Guest Inquirer',
        'email' => 'guest.inq@example.com',
        'subject' => 'Property inquiry',
        'message' => 'Is this property still available for inspection next week?',
    ]);
    $contactResponse->assertRedirect(route('register'));
    $contactResponse->assertSessionHas('signup_prompt', function ($prompt) {
        return str_contains($prompt, 'Register / Login to continue');
    });

    // 4. Attempting to favorite / save property
    $favResponse = $this->post(route('properties.favorite', $property));
    $favResponse->assertRedirect(route('register'));
    $favResponse->assertSessionHas('signup_prompt', function ($prompt) {
        return str_contains($prompt, 'Register / Login to continue');
    });
});

test('admin can assign another role to a guest user at any time', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $guestUser = User::factory()->create(['role' => 'guest', 'email' => 'newguest@example.com']);

    $response = $this->actingAs($admin)->put(route('dashboard.users.update', $guestUser), [
        'name' => 'Promoted Guest User',
        'email' => 'newguest@example.com',
        'role' => 'buyer',
    ]);

    $response->assertRedirect();
    expect($guestUser->fresh()->role)->toBe('buyer');
});

/*
|--------------------------------------------------------------------------
| 4. Admin Boundaries (No Impersonation, Cannot Open User Dashboards)
|--------------------------------------------------------------------------
*/

test('admin cannot open another users dashboard and cannot impersonate', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $targetBuyer = User::factory()->create(['role' => 'buyer']);

    // Admin attempting to open buyer personal dashboard gets 403 Forbidden
    $buyerDeskResponse = $this->actingAs($admin)->get(route('buyer.dashboard'));
    $buyerDeskResponse->assertForbidden();

    // Admin attempting to open tenant personal dashboard gets 403 Forbidden
    $tenantDeskResponse = $this->actingAs($admin)->get(route('tenant.dashboard'));
    $tenantDeskResponse->assertForbidden();

    // Admin attempting to open guest personal dashboard gets 403 Forbidden
    $guestDeskResponse = $this->actingAs($admin)->get(route('guest.dashboard'));
    $guestDeskResponse->assertForbidden();

    // Admin attempting to open client portal gets 403 Forbidden
    $clientPortalResponse = $this->actingAs($admin)->get(route('client.portal'));
    $clientPortalResponse->assertForbidden();

    // Admin attempting to impersonate gets 403 Forbidden
    $impersonateResponse = $this->actingAs($admin)->post(route('dashboard.users.impersonate', $targetBuyer));
    $impersonateResponse->assertForbidden();
});

test('permission changes are strictly recorded in the audit log', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer']);
    $perm = Permission::where('key', 'offers.create')->first();

    $response = $this->actingAs($admin)->post(route('dashboard.users.permissions.update', $user), [
        'permission_mode' => 'custom',
        'granted_permission_ids' => [],
        'denied_permission_ids' => [$perm->id],
        'reason' => 'Revoking offers capability per compliance policy',
    ]);

    $response->assertRedirect();

    $audit = AuditLog::where('action', 'user.permissions_assigned')
        ->where('entity_id', $user->id)
        ->first();

    expect($audit)->not()->toBeNull();
    expect($audit->user_id)->toBe($admin->id);
    expect($audit->reason)->toBe('Revoking offers capability per compliance policy');
    expect($audit->created_at)->not()->toBeNull();
});
