<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Immutable audit trail of every inbound payment-gateway callback.
     *
     * Every call - accepted or rejected - is recorded, including signature
     * failures, so that unauthorised webhook probing is visible to finance.
     */
    public function up(): void
    {
        Schema::create('payment_gateway_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 50)->nullable();
            $table->string('event', 60);
            $table->string('outcome', 30);
            $table->string('gateway_reference', 150)->nullable();
            $table->string('transaction_number', 100)->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string('signature', 255)->nullable();
            $table->boolean('signature_valid')->default(false);
            $table->unsignedSmallInteger('response_code')->default(200);
            $table->json('payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['provider', 'created_at']);
            $table->index('gateway_reference');
            $table->index('transaction_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_logs');
    }
};
