<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Ethiopian Transaction & Payment Engine (SRS §78–§83).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique()->index();
            $table->string('payable_type')->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payer_name');
            $table->string('payer_email');
            $table->string('payer_phone')->nullable();
            $table->string('payment_type'); // earnest_deposit, stay_booking, unit_reservation, rent_installment, service_fee
            $table->string('payment_method'); // telebirr, cbe_birr, bank_transfer, chapa, cash
            $table->string('bank_name')->nullable(); // Commercial Bank of Ethiopia (CBE), Awash Bank, etc.
            $table->string('bank_account_number')->nullable();
            $table->string('bank_transaction_reference')->nullable(); // FT number, Telebirr txn ID
            $table->string('deposit_slip_path')->nullable();
            $table->string('currency', 10)->default('ETB');
            $table->decimal('amount', 15, 2);
            $table->decimal('platform_fee', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, completed, failed, refunded, cancelled
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('payment_metadata')->nullable();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id']);
            $table->index(['status', 'payment_method']);
        });

        Schema::create('financial_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('ledger_code')->unique()->index();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('entry_type'); // credit, debit
            $table->string('category'); // booking_revenue, platform_fee, agent_commission, developer_deposit, tax_withholding, payout, earnest_deposit
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('ETB');
            $table->string('description');
            $table->string('reference')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['entry_type', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_ledgers');
        Schema::dropIfExists('payments');
    }
};
