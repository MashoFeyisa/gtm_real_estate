<?php

use App\Models\Agent;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->officer = User::create([
        'name' => 'Officer Kalkidan',
        'email' => 'officer.desk2@example.com',
        'password' => Hash::make('password123'),
        'role' => 'verification_officer',
    ]);

    $this->buyer = User::create([
        'name' => 'Buyer Almaz',
        'email' => 'buyer.verification@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->property = Property::create([
        'title' => 'Documented Property',
        'slug' => 'documented-property',
        'price' => 12000000,
        'type' => 'sale',
        'status' => 'available',
        'is_active' => true,
    ]);

    $this->document = PropertyDocument::create([
        'property_id' => $this->property->id,
        'document_type' => 'title_deed_carta',
        'title' => 'Bole Title Deed',
        'file_path' => 'documents/bole-title-deed.pdf',
        'file_name' => 'bole-title-deed.pdf',
    ]);

    $this->company = Agent::create([
        'name' => 'Unverified Realty LLC',
        'email' => 'unverified.realty@example.com',
        'phone' => '+251 91 222 3344',
        'license_verified' => false,
    ]);

    $this->developerProject = Project::create([
        'project_code' => 'ETH-DEV-AA-2026-VERIFY',
        'name' => 'Verification Towers',
        'slug' => 'verification-towers',
        'developer_name' => 'Rising Developers',
        'city' => 'Addis Ababa',
        'project_type' => 'mixed_use',
        'construction_status' => 'under_construction',
        'starting_price' => 15000000,
    ]);
});

test('verification desk renders decision buttons for every verification queue', function () {
    $response = $this->actingAs($this->officer)->get(route('role.portal'));

    $response->assertOk();
    $response->assertSee('Property Documents');
    $response->assertSee('Company Verifications');
    $response->assertSee('Developer Verifications');
    $response->assertSee('Bole Title Deed');
    $response->assertSee('Unverified Realty LLC');
    $response->assertSee('Verify');
});

test('verification officer can approve or reject a property document', function () {
    $this->actingAs($this->officer)
        ->put(route('properties.documents.update-status', [$this->property, $this->document]), [
            'status' => 'verified',
            'verification_notes' => 'Title deed matches the registry.',
        ])
        ->assertRedirect();

    $document = $this->document->fresh();

    expect($document->status)->toBe('verified');
    expect($document->verified_by)->toBe($this->officer->id);
    expect($document->verified_at)->not()->toBeNull();

    $this->actingAs($this->officer)
        ->put(route('properties.documents.update-status', [$this->property, $this->document->fresh()]), [
            'status' => 'rejected',
        ])
        ->assertRedirect();

    expect($this->document->fresh()->status)->toBe('rejected');
});

test('verification officer can certify property verification level', function () {
    $this->actingAs($this->officer)
        ->put(route('properties.verification.update', $this->property), [
            'verification_level' => 4,
            'verification_status' => 'verified',
            'verification_notes' => 'Ownership documents verified.',
        ])
        ->assertRedirect();

    $property = $this->property->fresh();

    expect($property->verification_level)->toBe(4);
    expect($property->verification_status)->toBe('verified');
});

test('verification officer can verify and revoke companies', function () {
    $this->actingAs($this->officer)
        ->post(route('verification.companies.decision', $this->company), ['decision' => 'verify'])
        ->assertRedirect();

    expect($this->company->fresh()->license_verified)->toBeTrue();

    $this->actingAs($this->officer)
        ->post(route('verification.companies.decision', $this->company->fresh()), ['decision' => 'revoke'])
        ->assertRedirect();

    expect($this->company->fresh()->license_verified)->toBeFalse();
});

test('verification officer can verify and revoke developers', function () {
    $this->actingAs($this->officer)
        ->post(route('verification.developers.decision', $this->developerProject), ['decision' => 'verify'])
        ->assertRedirect();

    expect($this->developerProject->fresh()->developer_verified)->toBeTrue();

    $this->actingAs($this->officer)
        ->post(route('verification.developers.decision', $this->developerProject->fresh()), ['decision' => 'revoke'])
        ->assertRedirect();

    expect($this->developerProject->fresh()->developer_verified)->toBeFalse();
});

test('verified companies leave the pending verification queue', function () {
    $this->company->update(['license_verified' => true]);

    $response = $this->actingAs($this->officer)->get(route('role.portal'));

    $response->assertOk();
    $response->assertDontSee('Unverified Realty LLC');
});

test('non verification officers cannot make verification decisions', function () {
    $this->actingAs($this->buyer)
        ->post(route('verification.companies.decision', $this->company), ['decision' => 'verify'])
        ->assertForbidden();

    $this->actingAs($this->buyer)
        ->post(route('verification.developers.decision', $this->developerProject), ['decision' => 'verify'])
        ->assertForbidden();

    $this->actingAs($this->buyer)
        ->put(route('properties.documents.update-status', [$this->property, $this->document]), ['status' => 'verified'])
        ->assertForbidden();

    $this->actingAs($this->buyer)
        ->put(route('properties.verification.update', $this->property), [
            'verification_level' => 5,
            'verification_status' => 'verified',
        ])
        ->assertForbidden();

    expect($this->company->fresh()->license_verified)->toBeFalse();
    expect($this->developerProject->fresh()->developer_verified)->toBeFalse();
    expect($this->document->fresh()->status)->toBe('pending');
    expect($this->property->fresh()->verification_level)->toBe(0);
});

test('revoking the document permission blocks document decisions', function () {
    UserPermissionOverride::create([
        'user_id' => $this->officer->id,
        'permission_id' => Permission::query()->where('key', 'documents.review_property')->value('id'),
        'granted' => false,
    ]);

    $this->actingAs($this->officer->fresh())
        ->put(route('properties.documents.update-status', [$this->property, $this->document]), ['status' => 'verified'])
        ->assertForbidden();

    expect($this->document->fresh()->status)->toBe('pending');
});

test('revoking the company permission blocks company decisions', function () {
    UserPermissionOverride::create([
        'user_id' => $this->officer->id,
        'permission_id' => Permission::query()->where('key', 'verifications.company')->value('id'),
        'granted' => false,
    ]);

    $this->actingAs($this->officer->fresh())
        ->post(route('verification.companies.decision', $this->company), ['decision' => 'verify'])
        ->assertForbidden();

    expect($this->company->fresh()->license_verified)->toBeFalse();
});

test('admins keep the full verification controls', function () {
    $admin = User::create([
        'name' => 'Verification Admin',
        'email' => 'admin.verification@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->put(route('properties.documents.update-status', [$this->property, $this->document]), ['status' => 'verified'])
        ->assertRedirect();

    $this->actingAs($admin)
        ->post(route('verification.companies.decision', $this->company), ['decision' => 'verify'])
        ->assertRedirect();

    expect($this->document->fresh()->status)->toBe('verified');
    expect($this->company->fresh()->license_verified)->toBeTrue();
});
