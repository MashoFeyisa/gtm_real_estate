<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('property_offers', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('lease_id')->nullable()->constrained('leases')->nullOnDelete();
            $table->string('closing_status')->default('pending'); // pending, agreement_generated, deposit_pending, deposit_paid, closed, cancelled
            $table->text('closing_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_offers', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['lease_id']);
            $table->dropColumn(['order_id', 'lease_id', 'closing_status', 'closing_notes']);
        });
    }
};
