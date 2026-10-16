<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Group the debit and credit legs of a single economic event so the
     * double-entry books can be audited (and proven to balance) per transaction.
     */
    public function up(): void
    {
        Schema::table('financial_ledgers', function (Blueprint $table) {
            $table->string('transaction_group')->nullable()->after('payment_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_ledgers', function (Blueprint $table) {
            $table->dropIndex(['transaction_group']);
            $table->dropColumn('transaction_group');
        });
    }
};
