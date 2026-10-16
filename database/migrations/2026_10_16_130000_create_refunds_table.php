<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * First-class refund records (SRS §132, §170).
 *
 * A refund is money leaving the business, so it is recorded as a fact of its
 * own rather than only as a status on the payment: the amount, the fee and VAT
 * it reversed, the credit note that documents the VAT reversal, and the
 * idempotency key that stops a retry from refunding twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('fee_reversed', 15, 2)->default(0);
            $table->decimal('vat_reversed', 15, 2)->default(0);
            $table->string('reason');
            $table->string('ledger_group')->nullable();
            $table->string('credit_note_number')->nullable()->index();
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('idempotency_hash')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
