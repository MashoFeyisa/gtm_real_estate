<?php

use App\Models\Lease;
use App\Models\Listing;
use App\Models\Permission;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyReport;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function personaDeskUser(string $role, string $email): User
{
    return User::create([
        'name' => ucfirst(str_replace('_', ' ', $role)),
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => $role,
    ]);
}

function personaDeskProperty(string $title = 'Desk Test House'): Property
{
    return Property::create([
        'title' => $title,
        'slug' => str()->slug($title).'-'.str()->random(5),
        'price' => 4500000,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
    ]);
}

test('every persona without a dedicated dashboard gets its own sectioned desk', function (string $role, string $label) {
    $user = personaDeskUser($role, $role.'@example.com');

    $response = $this->actingAs($user)->get(route('role.portal'));

    $response->assertOk();
    $response->assertSee($label.' Dashboard');
    $response->assertSee('Signed in');
})->with([
    'property owner' => ['property_owner', 'Property Owner'],
    'landlord' => ['landlord', 'Landlord'],
    'property manager' => ['property_manager', 'Property Manager'],
    'service provider' => ['service_provider', 'Professional Service Provider'],
    'moderator' => ['moderator', 'Moderator'],
    'verification officer' => ['verification_officer', 'Verification Officer'],
    'customer support' => ['customer_support', 'Customer Support'],
]);

test('moderator desk shows listing review and report queues with real data', function () {
    $moderator = personaDeskUser('moderator', 'moderator.desk@example.com');

    personaDeskProperty('Ordinary Family House');

    $property = personaDeskProperty('Reported Luxury Condo');
    Listing::create([
        'property_id' => $property->id,
        'listing_type' => 'sale',
        'title' => 'Suspicious Cheap Villa',
        'price' => 100.00,
        'status' => 'pending_review',
    ]);
    PropertyReport::create([
        'report_number' => 'RPT-DESK-0001',
        'subject' => 'Scam advertisement detected',
        'reason' => 'fraud',
        'description' => 'Listed price is far below market value and the seller requests an off-platform wire transfer.',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($moderator)->get(route('role.portal'));

    $response->assertOk();
    $response->assertSee('Listing Reviews');
    $response->assertSee('Reported Listings');
    $response->assertSee('Suspicious Cheap Villa');
    $response->assertSee('Scam advertisement detected');
    $response->assertSee('Review listings');
});

test('landlord desk is scoped to their own leases and tenants', function () {
    $landlord = personaDeskUser('landlord', 'landlord.desk@example.com');
    $otherLandlord = personaDeskUser('landlord', 'landlord.other@example.com');

    $property = personaDeskProperty();

    Lease::create([
        'lease_code' => 'LSE-MINE-001',
        'property_id' => $property->id,
        'landlord_user_id' => $landlord->id,
        'tenant_name' => 'Tenant Tesfaye',
        'tenant_email' => 'tesfaye@example.com',
        'tenant_phone' => '+251 911 000 111',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'monthly_rent' => 55000,
    ]);
    Lease::create([
        'lease_code' => 'LSE-THEIRS-002',
        'property_id' => $property->id,
        'landlord_user_id' => $otherLandlord->id,
        'tenant_name' => 'Private Tenant',
        'tenant_email' => 'private@example.com',
        'tenant_phone' => '+251 911 000 222',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'monthly_rent' => 60000,
    ]);

    $response = $this->actingAs($landlord)->get(route('role.portal'));

    $response->assertOk();
    $response->assertSee('Tenants');
    $response->assertSee('Tenant Tesfaye');
    $response->assertSee('LSE-MINE-001');
    $response->assertDontSee('Private Tenant');
    $response->assertDontSee('LSE-THEIRS-002');
});

test('verification officer desk shows the property document review queue', function () {
    $officer = personaDeskUser('verification_officer', 'officer.desk@example.com');

    $property = personaDeskProperty('Documented Raw Land');
    PropertyDocument::create([
        'property_id' => $property->id,
        'document_type' => 'title_deed_carta',
        'title' => 'Title Deed Bole Area',
        'file_path' => 'documents/bole-deed.pdf',
        'file_name' => 'bole-deed.pdf',
    ]);

    $response = $this->actingAs($officer)->get(route('role.portal'));

    $response->assertOk();
    $response->assertSee('Property Documents');
    $response->assertSee('Title Deed Bole Area');
    $response->assertSee('Review property documents');
});

test('revoking a permission hides its section from the persona desk', function () {
    $moderator = personaDeskUser('moderator', 'moderator.revoke@example.com');

    UserPermissionOverride::create([
        'user_id' => $moderator->id,
        'permission_id' => Permission::query()->where('key', 'listings.review')->value('id'),
        'granted' => false,
    ]);

    $response = $this->actingAs($moderator->fresh())->get(route('role.portal'));

    $response->assertOk();
    $response->assertDontSee('Listing Reviews');
    $response->assertSee('Reported Listings');
});

test('roles with their own dashboards cannot open the persona desk', function () {
    $buyer = personaDeskUser('buyer', 'buyer.desk@example.com');

    $this->actingAs($buyer)->get(route('role.portal'))->assertForbidden();

    expect($buyer->dashboard_url)->toBe(route('buyer.dashboard'));
});
