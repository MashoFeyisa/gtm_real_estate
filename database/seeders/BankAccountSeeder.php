<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'bank_name' => 'CBE Bank',
                'account_name' => 'GTM Real Estate PLC',
                'account_number' => '1000 2837 4920 1',
                'branch' => 'Finfinne Branch',
                'type' => 'bank',
                'instructions' => 'Deposit at any Commercial Bank of Ethiopia branch or via CBE Mobile Banking.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'bank_name' => 'Awash Bank',
                'account_name' => 'GTM Real Estate PLC',
                'account_number' => '0132 0492 8172 00',
                'branch' => 'Head Office Branch',
                'type' => 'bank',
                'instructions' => 'Deposit at any Awash Bank branch or Awash Birr / Mobile Banking.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'bank_name' => 'Dashen Bank',
                'account_name' => 'GTM Real Estate PLC',
                'account_number' => '0029 3847 2918 01',
                'branch' => 'Bole Branch',
                'type' => 'bank',
                'instructions' => 'Deposit at any Dashen Bank branch or Amole / Mobile Banking.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'bank_name' => 'CBE Birr Merchant Account',
                'account_name' => 'GTM Real Estate Company',
                'account_number' => '284920',
                'branch' => 'Digital Merchant Desk',
                'type' => 'cbe_birr',
                'instructions' => 'Transfer directly to GTM CBE Merchant Till / Shortcode: 284920.',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($accounts as $account) {
            BankAccount::updateOrCreate(
                ['bank_name' => $account['bank_name'], 'type' => $account['type']],
                $account
            );
        }
    }
}
