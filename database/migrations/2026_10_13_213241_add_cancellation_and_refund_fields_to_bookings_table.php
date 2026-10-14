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
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('bookings', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
            if (! Schema::hasColumn('bookings', 'refund_amount')) {
                $table->decimal('refund_amount', 14, 2)->default(0)->after('cancellation_reason');
            }
            if (! Schema::hasColumn('bookings', 'refund_status')) {
                $table->string('refund_status')->nullable()->after('refund_amount'); // e.g. none, pending_disbursement, refunded
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancellation_reason', 'refund_amount', 'refund_status']);
        });
    }
};
