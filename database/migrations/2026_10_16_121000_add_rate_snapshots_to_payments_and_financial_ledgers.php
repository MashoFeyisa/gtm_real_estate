<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Freeze the rate a record was written with (SRS §171).
     *
     * Every money record stores the rate that produced its amount, so a later
     * rate change can never silently rewrite history: reports read the snapshot
     * and never re-derive an old fee or tax from today's rate.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('platform_fee_rate', 8, 4)->nullable()->after('platform_fee');
            $table->decimal('vat_rate', 8, 4)->nullable()->after('tax_amount');
        });

        Schema::table('financial_ledgers', function (Blueprint $table) {
            $table->json('rate_snapshot')->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['platform_fee_rate', 'vat_rate']);
        });

        Schema::table('financial_ledgers', function (Blueprint $table) {
            $table->dropColumn('rate_snapshot');
        });
    }
};
