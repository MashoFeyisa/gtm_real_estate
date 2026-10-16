<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A durable flag that a money event could not safely roll a payable back
 * because the payable had already moved on (countered, accepted, re-let...).
 *
 * Instead of forcing a state change that would destroy a later business fact,
 * the refund/rejection records a flag for the finance desk to review.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_review_flags', function (Blueprint $table) {
            $table->id();
            $table->string('reviewable_type');
            $table->unsignedBigInteger('reviewable_id');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('reason');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reviewable_type', 'reviewable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_review_flags');
    }
};
