<?php

use App\Models\BankAccount;
use App\Models\Property;
use App\Models\User;

test('payment modal displays default CBE Bank, Awash Bank, Dashen Bank, and CBE Birr merchant account', function () {
    $property = Property::factory()->create([
        'title' => 'Luxury Bole Penthouse',
        'slug' => 'luxury-bole-penthouse',
    ]);

    $response = $this->get(route('properties.show', $property->slug));

    $response->assertOk()
        ->assertSee('1000 2837 4920 1')
        ->assertSee('0132 0492 8172 00')
        ->assertSee('0029 3847 2918 01')
        ->assertSee('284920')
        ->assertSee('CBE Bank')
        ->assertSee('Awash Bank')
        ->assertSee('Dashen Bank');
});

test('company user can view bank accounts in company dashboard', function () {
    $companyUser = User::factory()->create([
        'role' => 'company',
        'name' => 'Corporate Treasury Executive',
    ]);

    $response = $this->actingAs($companyUser)->get(route('company.dashboard'));

    $response->assertOk()
        ->assertSee('Bank Accounts')
        ->assertSee('1000 2837 4920 1')
        ->assertSee('0132 0492 8172 00')
        ->assertSee('0029 3847 2918 01')
        ->assertSee('284920');
});

test('unauthorized users cannot manage company bank accounts', function () {
    $buyerUser = User::factory()->create(['role' => 'buyer']);
    $account = BankAccount::first();

    $this->actingAs($buyerUser)
        ->post(route('company.bank-accounts.store'), [
            'bank_name' => 'Hacker Bank',
            'account_number' => '9999 9999 9999 99',
            'type' => 'bank',
        ])
        ->assertForbidden();

    if ($account) {
        $this->actingAs($buyerUser)
            ->put(route('company.bank-accounts.update', $account), [
                'bank_name' => 'Modified Bank',
                'account_number' => '1111 1111 1111 11',
                'type' => 'bank',
            ])
            ->assertForbidden();

        $this->actingAs($buyerUser)
            ->delete(route('company.bank-accounts.destroy', $account))
            ->assertForbidden();
    }
});

test('company user can create a new bank account and cbe birr merchant account', function () {
    $companyUser = User::factory()->create(['role' => 'company']);

    $response = $this->actingAs($companyUser)->post(route('company.bank-accounts.store'), [
        'bank_name' => 'Bank of Abyssinia',
        'account_name' => 'GTM Real Estate PLC',
        'account_number' => '8888 7777 6666 55',
        'branch' => 'Bole Medhanialem Branch',
        'type' => 'bank',
        'instructions' => 'Deposit with property reference code.',
        'is_active' => '1',
        'sort_order' => 5,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('bank_accounts', [
        'bank_name' => 'Bank of Abyssinia',
        'account_number' => '8888 7777 6666 55',
        'type' => 'bank',
        'is_active' => true,
    ]);

    // Create a new CBE Birr Merchant account
    $response = $this->actingAs($companyUser)->post(route('company.bank-accounts.store'), [
        'bank_name' => 'CBE Birr Secondary Till',
        'account_name' => 'GTM Real Estate Co',
        'account_number' => '392810',
        'branch' => 'Digital Till 2',
        'type' => 'cbe_birr',
        'is_active' => '1',
        'sort_order' => 6,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('bank_accounts', [
        'bank_name' => 'CBE Birr Secondary Till',
        'account_number' => '392810',
        'type' => 'cbe_birr',
    ]);
});

test('company user can update existing bank account and cbe birr merchant account', function () {
    $companyUser = User::factory()->create(['role' => 'company']);

    $cbeAccount = BankAccount::where('bank_name', 'CBE Bank')->first()
        ?? BankAccount::create([
            'bank_name' => 'CBE Bank',
            'account_number' => '1000 2837 4920 1',
            'type' => 'bank',
            'is_active' => true,
        ]);

    $response = $this->actingAs($companyUser)->put(route('company.bank-accounts.update', $cbeAccount), [
        'bank_name' => 'Commercial Bank of Ethiopia (CBE Main)',
        'account_name' => 'GTM Luxury Properties PLC',
        'account_number' => '1000 9999 8888 77',
        'branch' => 'Gofa Branch',
        'type' => 'bank',
        'instructions' => 'Deposit slips must be uploaded.',
        'sort_order' => 1,
        'is_active' => '1',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('bank_accounts', [
        'id' => $cbeAccount->id,
        'bank_name' => 'Commercial Bank of Ethiopia (CBE Main)',
        'account_number' => '1000 9999 8888 77',
    ]);

    // Update CBE Birr merchant account
    $merchantAccount = BankAccount::where('type', 'cbe_birr')->first()
        ?? BankAccount::create([
            'bank_name' => 'CBE Birr Merchant Account',
            'account_number' => '284920',
            'type' => 'cbe_birr',
            'is_active' => true,
        ]);

    $response = $this->actingAs($companyUser)->put(route('company.bank-accounts.update', $merchantAccount), [
        'bank_name' => 'CBE Birr VIP Merchant',
        'account_name' => 'GTM Enterprise',
        'account_number' => '554433',
        'type' => 'cbe_birr',
        'sort_order' => 1,
        'is_active' => '1',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('bank_accounts', [
        'id' => $merchantAccount->id,
        'account_number' => '554433',
    ]);
});

test('company user can delete bank account', function () {
    $companyUser = User::factory()->create(['role' => 'company']);

    $account = BankAccount::create([
        'bank_name' => 'Temporary Test Bank',
        'account_number' => '1234 5678 9012 34',
        'type' => 'bank',
        'is_active' => true,
    ]);

    $response = $this->actingAs($companyUser)->delete(route('company.bank-accounts.destroy', $account));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('bank_accounts', [
        'id' => $account->id,
    ]);
});

test('updated bank accounts and cbe birr merchant code appear dynamically in property details payment modal', function () {
    $property = Property::factory()->create([
        'title' => 'Sunrise Luxury Villa',
        'slug' => 'sunrise-luxury-villa-pay-test',
    ]);

    // Update CBE Bank account number to a distinct new number
    $cbe = BankAccount::where('type', 'bank')->first();
    if ($cbe) {
        $cbe->update([
            'account_number' => '1000 5555 4444 33',
        ]);
    }

    // Update CBE Birr merchant code to a distinct code
    $merchant = BankAccount::where('type', 'cbe_birr')->first();
    if ($merchant) {
        $merchant->update([
            'account_number' => '998877',
        ]);
    }

    $response = $this->get(route('properties.show', $property->slug));

    $response->assertOk()
        ->assertSee('1000 5555 4444 33')
        ->assertSee('998877');
});
