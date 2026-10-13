<?php

use App\Models\Branch;
use App\Models\User;

test('company actor user is redirected to company dashboard upon login', function () {
    $companyUser = User::factory()->create([
        'role' => 'company',
        'password' => bcrypt('CompanyPass123!'),
    ]);

    $response = $this->post('/login', [
        'email' => $companyUser->email,
        'password' => 'CompanyPass123!',
    ]);

    $response->assertRedirect('/company');
    expect($companyUser->isCompany())->toBeTrue();
    expect($companyUser->dashboard_url)->toBe(route('company.dashboard'));
});

test('company dashboard renders gtm real estate company enterprise beast display and 7 management pillars', function () {
    $companyUser = User::factory()->create([
        'role' => 'company',
        'name' => 'GTM Executive Director',
    ]);

    $branch = Branch::create([
        'name' => 'GTM Real Estate HQ - Bole Medhanialem',
        'code' => 'GTM-BOLE',
        'city' => 'Addis Ababa',
        'region' => 'Addis Ababa',
        'manager_name' => 'GTM Operations Director',
        'phone' => '+251911000001',
        'status' => 'active',
        'is_headquarters' => true,
    ]);

    $client = User::factory()->create([
        'role' => 'client',
        'name' => 'Test Investor',
    ]);

    $response = $this->actingAs($companyUser)->get(route('company.dashboard'));

    $response->assertOk();
    $response->assertSee('GTM Real Estate Company');
    $response->assertSee('Enterprise Hub');

    // 7 Pillars Verification in Company Dashboard
    $response->assertSee('data-company-section="overview"', false);
    $response->assertSee('data-company-section="branches"', false);
    $response->assertSee('data-company-section="agents"', false);
    $response->assertSee('data-company-section="listings"', false);
    $response->assertSee('data-company-section="properties"', false);
    $response->assertSee('data-company-section="clients"', false);
    $response->assertSee('data-company-section="transactions"', false);
    $response->assertSee('data-company-section="reports"', false);

    // Verify branch details appear
    $response->assertSee('GTM Real Estate HQ - Bole Medhanialem');
    $response->assertSee('GTM-BOLE');

    // Verify client appears in clients desk
    $response->assertSee('Test Investor');

    // Verify client reports & trust desk from admin dashboard appears
    $response->assertSee('Client Reports &amp; Incidents', false);
    $response->assertSee('Trust &amp; Safety', false);
});

test('company actor can create, update and delete branches from company dashboard', function () {
    $companyUser = User::factory()->create([
        'role' => 'company',
    ]);

    // 1. Create branch
    $storeResponse = $this->actingAs($companyUser)->post(route('company.branches.store'), [
        'name' => 'Seyid CMC Heights Branch',
        'code' => 'SRE-CMC-02',
        'city' => 'Addis Ababa',
        'sub_city' => 'Yeka',
        'region' => 'Addis Ababa',
        'address' => 'CMC St. Michael Square',
        'manager_name' => 'Dawit Tadesse',
        'phone' => '+251911223344',
        'status' => 'active',
        'is_headquarters' => false,
    ]);

    $storeResponse->assertRedirect(route('company.dashboard', ['section' => 'branches']));
    $storeResponse->assertSessionHas('success');

    $branch = Branch::where('code', 'SRE-CMC-02')->first();
    expect($branch)->not->toBeNull();
    expect($branch->name)->toBe('Seyid CMC Heights Branch');

    // 2. Update branch
    $updateResponse = $this->actingAs($companyUser)->put(route('company.branches.update', $branch), [
        'name' => 'Seyid CMC Heights Premier Office',
        'code' => 'SRE-CMC-02',
        'city' => 'Addis Ababa',
        'sub_city' => 'Yeka',
        'region' => 'Addis Ababa',
        'address' => 'CMC St. Michael Square, 4th Floor',
        'manager_name' => 'Dawit Tadesse Sr.',
        'phone' => '+251911223344',
        'status' => 'active',
        'is_headquarters' => false,
    ]);

    $updateResponse->assertRedirect(route('company.dashboard', ['section' => 'branches']));
    expect($branch->fresh()->name)->toBe('Seyid CMC Heights Premier Office');

    // 3. Delete branch
    $deleteResponse = $this->actingAs($companyUser)->delete(route('company.branches.destroy', $branch));
    $deleteResponse->assertRedirect(route('company.dashboard', ['section' => 'branches']));
    expect(Branch::where('id', $branch->id)->exists())->toBeFalse();
});

test('company actor can register clients into CRM', function () {
    $companyUser = User::factory()->create([
        'role' => 'company',
    ]);

    $response = $this->actingAs($companyUser)->post(route('company.clients.store'), [
        'name' => 'Hanan Mohammed',
        'email' => 'hanan.company.client@example.com',
        'role' => 'buyer',
        'password' => 'ClientPass123!#',
    ]);

    $response->assertRedirect(route('company.dashboard', ['section' => 'clients']));
    $createdClient = User::where('email', 'hanan.company.client@example.com')->first();
    expect($createdClient)->not->toBeNull();
    expect($createdClient->role)->toBe('buyer');
});

test('company actor can update and delete clients from CRM', function () {
    $companyUser = User::factory()->create([
        'role' => 'company',
    ]);

    $client = User::factory()->create([
        'role' => 'client',
        'name' => 'Original Name',
        'email' => 'client.to.update@example.com',
    ]);

    // Update
    $updateResponse = $this->actingAs($companyUser)->put(route('company.clients.update', $client), [
        'name' => 'Updated Client Name',
        'email' => 'client.updated@example.com',
        'role' => 'buyer',
    ]);

    $updateResponse->assertRedirect(route('company.dashboard', ['section' => 'clients']));
    expect($client->fresh()->name)->toBe('Updated Client Name');
    expect($client->fresh()->role)->toBe('buyer');

    // Delete
    $deleteResponse = $this->actingAs($companyUser)->delete(route('company.clients.destroy', $client));
    $deleteResponse->assertRedirect(route('company.dashboard', ['section' => 'clients']));
    expect(User::where('id', $client->id)->exists())->toBeFalse();
});

test('company dashboard includes universal payment modal and branch creation controls', function () {
    $companyUser = User::factory()->create([
        'role' => 'company',
    ]);

    $response = $this->actingAs($companyUser)->get(route('company.dashboard'));
    $response->assertOk();
    $response->assertSee('id="universal-payment-modal"', false);
    $response->assertSee('openUniversalPaymentModal', false);
    $response->assertSee('toggle-create-form', false);
    $response->assertSee('branches-create-panel', false);
});

test('regular client cannot access company dashboard', function () {
    $client = User::factory()->create([
        'role' => 'client',
    ]);

    $response = $this->actingAs($client)->get(route('company.dashboard'));
    $response->assertForbidden();
});

test('admin can create company account and that company account can access company dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $createResponse = $this->actingAs($admin)->post(route('dashboard.users.store'), [
        'name' => 'Seyid Real Estate Enterprise Account',
        'email' => 'admin_created_company@seyidrealestate.et',
        'role' => 'company',
        'phone' => '+251911998877',
        'password' => 'SecureCompany2026!',
        'password_confirmation' => 'SecureCompany2026!',
    ]);

    $createResponse->assertRedirect(route('dashboard', ['section' => 'users']));
    $createResponse->assertSessionHas('success');

    $createdCompany = User::where('email', 'admin_created_company@seyidrealestate.et')->first();
    expect($createdCompany)->not->toBeNull();
    expect($createdCompany->role)->toBe('company');
    expect($createdCompany->isCompany())->toBeTrue();
    expect($createdCompany->canAccessCompanyDashboard())->toBeTrue();

    // Verify company login and dashboard access
    $dashResponse = $this->actingAs($createdCompany)->get(route('company.dashboard'));
    $dashResponse->assertOk();
    $dashResponse->assertSee('GTM Real Estate Company');
});

test('landing page includes language switcher while portals and dashboards inherit translation without switcher widget', function () {
    // 1. Landing page has language switcher
    $homeResponse = $this->get(route('home'));
    $homeResponse->assertOk()
        ->assertSee('id="language-dropdown-toggle"', false)
        ->assertSee('data-set-language="am"', false)
        ->assertSee('data-set-language="om"', false);

    // 2. Company dashboard does not include manual switcher widget
    $companyUser = User::factory()->create(['role' => 'company']);
    $companyResponse = $this->actingAs($companyUser)->get(route('company.dashboard'));
    $companyResponse->assertOk()
        ->assertDontSee('id="language-dropdown-toggle"', false)
        ->assertDontSee('data-language-switcher-wrapper', false);

    // 3. Admin dashboard does not include manual switcher widget
    $adminUser = User::factory()->create(['role' => 'admin']);
    $adminResponse = $this->actingAs($adminUser)->get(route('dashboard'));
    $adminResponse->assertOk()
        ->assertDontSee('id="language-dropdown-toggle"', false)
        ->assertDontSee('data-language-switcher-wrapper', false);

    // 4. Client portal does not include manual switcher widget
    $clientUser = User::factory()->create(['role' => 'client']);
    $clientResponse = $this->actingAs($clientUser)->get(route('client.portal'));
    $clientResponse->assertOk()
        ->assertDontSee('id="language-dropdown-toggle"', false)
        ->assertDontSee('data-language-switcher-wrapper', false);
});
