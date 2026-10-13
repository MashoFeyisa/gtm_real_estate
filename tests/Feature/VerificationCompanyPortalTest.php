<?php

use App\Models\Permission;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->firm = User::create([
        'name' => 'Admas Verification Firm',
        'email' => 'admas.verification@example.com',
        'password' => Hash::make('password123'),
        'role' => 'verification_company',
    ]);

    $this->buyer = User::create([
        'name' => 'Firm Buyer',
        'email' => 'firm.buyer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->property = Property::create([
        'title' => 'Firm Verification Property',
        'slug' => 'firm-verification-property',
        'price' => 7400000,
        'type' => 'sale',
        'status' => 'available',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole',
        'verification_status' => 'pending',
        'verification_level' => 0,
    ]);

    $this->document = PropertyDocument::create([
        'property_id' => $this->property->id,
        'document_type' => 'title_deed_carta',
        'title' => 'Title Deed for Firm Review',
        'file_path' => 'documents/firm-title-deed.pdf',
        'file_name' => 'firm-title-deed.pdf',
        'status' => 'pending',
    ]);
});

test('verification company persona is catalogued with its own portal permissions and sections', function () {
    $persona = collect(config('personas.personas'))
        ->firstWhere('role', 'verification_company');

    expect($persona)->not()->toBeNull();
    expect($persona['label'])->toBe('Verification Company');
    expect($persona['portal'])->toBe('verification_company.portal');
    expect($persona['sections'])->toHaveCount(5);

    $permissionKeys = array_keys($persona['permissions']);

    expect($permissionKeys)->toContain('verification_jobs.manage');
    expect($permissionKeys)->toContain('property_documents.verify');
    expect($permissionKeys)->toContain('verification_reports.submit');
    expect($permissionKeys)->toContain('verification_payments.receive');
});

test('seeder grants the verification company role its default permissions', function () {
    $grantedKeys = $this->firm->grantedPermissionKeys();

    expect($grantedKeys)->toContain('verification_jobs.manage');
    expect($grantedKeys)->toContain('property_documents.verify');
    expect($grantedKeys)->toContain('verification_reports.submit');
    expect($grantedKeys)->toContain('verification_payments.receive');
    expect($grantedKeys)->not->toContain('payments.manage');
});

test('verification company lands on its own dashboard after login', function () {
    $response = $this->post(route('login'), [
        'email' => $this->firm->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('verification_company.portal'));
    expect($this->firm->dashboard_url)->toBe(route('verification_company.portal'));
});

test('verification company dashboard renders every section with its data', function () {
    $response = $this->actingAs($this->firm)->get(route('verification_company.portal'));

    $response->assertOk();
    $response->assertSee('Verification Company');
    $response->assertSee('Verification Jobs');
    $response->assertSee('Property Certifications');
    $response->assertSee('Submitted Reports');
    $response->assertSee('Verification Payments');
    $response->assertSee('Title Deed for Firm Review');
    $response->assertSee('Firm Verification Property');
});

test('verification company can verify or reject a property document', function () {
    $this->actingAs($this->firm)
        ->put(route('properties.documents.update-status', [$this->property, $this->document]), [
            'status' => 'verified',
            'verification_notes' => 'Title deed matches the registry.',
        ])
        ->assertRedirect();

    $document = $this->document->fresh();

    expect($document->status)->toBe('verified');
    expect($document->verified_by)->toBe($this->firm->id);
    expect($document->verified_at)->not()->toBeNull();

    $this->actingAs($this->firm)
        ->put(route('properties.documents.update-status', [$this->property, $this->document->fresh()]), [
            'status' => 'rejected',
        ])
        ->assertRedirect();

    expect($this->document->fresh()->status)->toBe('rejected');
});

test('verification company can certify a property verification level', function () {
    $this->actingAs($this->firm)
        ->put(route('properties.verification.update', $this->property), [
            'verification_level' => 4,
            'verification_status' => 'verified',
            'verification_notes' => 'Ownership and boundary verified on site.',
        ])
        ->assertRedirect();

    $property = $this->property->fresh();

    expect($property->verification_level)->toBe(4);
    expect($property->verification_status)->toBe('verified');
});

test('revoking permissions hides sections and blocks verification actions', function () {
    UserPermissionOverride::create([
        'user_id' => $this->firm->id,
        'permission_id' => Permission::query()->where('key', 'property_documents.verify')->value('id'),
        'granted' => false,
    ]);

    $response = $this->actingAs($this->firm->fresh())->get(route('verification_company.portal'));

    $response->assertOk();
    $response->assertDontSee('Verify');

    $this->actingAs($this->firm->fresh())
        ->put(route('properties.documents.update-status', [$this->property, $this->document]), ['status' => 'verified'])
        ->assertForbidden();

    expect($this->document->fresh()->status)->toBe('pending');
});

test('other roles cannot use the verification company desk or actions', function () {
    $this->actingAs($this->buyer)
        ->get(route('verification_company.portal'))
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

    expect($this->document->fresh()->status)->toBe('pending');
    expect($this->property->fresh()->verification_level)->toBe(0);
});

test('admins keep access to the verification company desk', function () {
    $admin = User::create([
        'name' => 'Firm Admin',
        'email' => 'firm.admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get(route('verification_company.portal'));

    $response->assertOk();
    $response->assertSee('Verification Company');
});

test('verification company is offered in the admin user management role dropdown', function () {
    $admin = User::create([
        'name' => 'Dropdown Admin',
        'email' => 'dropdown.admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get('/dashboard?section=users');

    $response->assertOk();
    $response->assertSee('<option value="verification_company"', false);
});
