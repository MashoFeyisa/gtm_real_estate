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
        Schema::table('commissions', function (Blueprint $table) {
            $table->decimal('withholding_tax_rate', 5, 2)->default(2.00)->after('commission_amount');
            $table->decimal('withholding_tax_amount', 12, 2)->default(0.00)->after('withholding_tax_rate');
            $table->decimal('net_payout_amount', 12, 2)->nullable()->after('withholding_tax_amount');
            $table->string('payout_method')->nullable()->after('net_payout_amount');
            $table->string('payout_reference')->nullable()->after('payout_method');
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete()->after('payout_reference');
            $table->string('voucher_number')->nullable()->unique()->after('disbursed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['disbursed_by']);
            $table->dropColumn([
                'withholding_tax_rate',
                'withholding_tax_amount',
                'net_payout_amount',
                'payout_method',
                'payout_reference',
                'disbursed_by',
                'voucher_number',
            ]);
        });
    }
};
