<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payout governance (SRS §194): maker-checker approval, an issued voucher, a
 * manual-disbursement marker (real transfers are not integrated), and an
 * idempotency key so a retried disbursement cannot pay twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->string('voucher_number')->nullable()->after('status');
            $table->string('disbursement_mode')->default('manual')->after('voucher_number');
            $table->foreignId('approved_by')->nullable()->after('disbursement_mode')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('idempotency_key')->nullable()->unique()->after('approved_at');
            $table->string('idempotency_hash')->nullable()->after('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['voucher_number', 'disbursement_mode', 'approved_at', 'idempotency_key', 'idempotency_hash']);
        });
    }
};
