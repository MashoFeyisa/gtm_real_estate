<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional second-person approval for a rate change.
 *
 * A row is approved by default (matching historic behaviour). When the
 * `finance.rates.require_second_approval` setting is on, a new row is written
 * `pending` and only becomes live once a *different* finance user approves it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_rates', function (Blueprint $table) {
            $table->string('status')->default('approved')->after('reason')->index();
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('finance_rates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['status', 'approved_at']);
        });
    }
};
