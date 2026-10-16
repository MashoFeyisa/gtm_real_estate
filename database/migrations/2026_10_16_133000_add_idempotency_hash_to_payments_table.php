<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The payload fingerprint that accompanies an idempotency key.
 *
 * Replaying the same key with the same payload returns the original result; the
 * same key with a different payload is a conflict and is rejected (HTTP 409).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('idempotency_hash')->nullable()->after('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('idempotency_hash');
        });
    }
};
