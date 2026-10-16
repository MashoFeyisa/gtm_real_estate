<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widen the bank-detail columns so they can hold ciphertext.
 *
 * Bank account numbers are now encrypted at rest (App\Casts\EncryptedString),
 * and a base64 ciphertext is much longer than the account number it protects,
 * so the columns must be text rather than a 255-char string.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->text('bank_name')->nullable()->change();
            $table->text('account_number')->nullable()->change();
        });

        if (Schema::hasColumn('payments', 'bank_account_number')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->text('bank_account_number')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->change();
            $table->string('account_number')->nullable()->change();
        });

        if (Schema::hasColumn('payments', 'bank_account_number')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('bank_account_number')->nullable()->change();
            });
        }
    }
};
