<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('account_name')->nullable();
            $table->string('account_number');
            $table->string('branch')->nullable();
            $table->string('type')->default('bank'); // 'bank' or 'cbe_birr'
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert the standard default bank accounts and CBE Birr merchant account
        DB::table('bank_accounts')->insert([
            [
                'bank_name' => 'CBE Bank',
                'account_name' => 'GTM Real Estate PLC',
                'account_number' => '1000 2837 4920 1',
                'branch' => 'Finfinne Branch',
                'type' => 'bank',
                'instructions' => 'Deposit at any Commercial Bank of Ethiopia branch or via CBE Mobile Banking.',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
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
                'created_at' => now(),
                'updated_at' => now(),
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
                'created_at' => now(),
                'updated_at' => now(),
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
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
