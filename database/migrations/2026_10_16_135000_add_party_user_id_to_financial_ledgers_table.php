<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attribute a money leg to the party it belongs to (agent, host, professional).
 *
 * Available balances are computed from the ledger, never stored: a party's
 * balance is the credits attributed to them minus the debits, less anything
 * held. This column is what makes that attribution possible.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_ledgers', function (Blueprint $table) {
            $table->foreignId('party_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->index(['party_user_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::table('financial_ledgers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('party_user_id');
        });
    }
};
