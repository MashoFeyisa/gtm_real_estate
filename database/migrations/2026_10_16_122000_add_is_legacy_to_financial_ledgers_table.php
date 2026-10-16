<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flag the single-sided rows written before the double-entry change.
     *
     * Old rows are never rewritten or deleted - they are only marked so a
     * report can tell a legacy, credit-only entry apart from a balanced
     * journal leg. `is_legacy` defaults to false, so every row written from
     * here on is current by construction.
     */
    public function up(): void
    {
        Schema::table('financial_ledgers', function (Blueprint $table) {
            $table->boolean('is_legacy')->default(false)->after('rate_snapshot')->index();
        });

        // Everything that already exists predates the balanced-journal writer.
        DB::table('financial_ledgers')->update(['is_legacy' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_ledgers', function (Blueprint $table) {
            $table->dropIndex(['is_legacy']);
            $table->dropColumn('is_legacy');
        });
    }
};
