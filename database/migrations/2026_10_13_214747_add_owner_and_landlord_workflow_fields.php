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
        Schema::table('property_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('property_documents', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('document_number');
            }
            if (! Schema::hasColumn('property_documents', 'notes')) {
                $table->text('notes')->nullable()->after('verification_notes');
            }
        });

        Schema::table('leases', function (Blueprint $table) {
            if (! Schema::hasColumn('leases', 'deposit_status')) {
                $table->string('deposit_status')->default('held')->after('security_deposit'); // held, partially_deducted, returned, retained
            }
            if (! Schema::hasColumn('leases', 'deposit_deductions')) {
                $table->decimal('deposit_deductions', 14, 2)->default(0)->after('deposit_status');
            }
            if (! Schema::hasColumn('leases', 'deposit_refunded')) {
                $table->decimal('deposit_refunded', 14, 2)->default(0)->after('deposit_deductions');
            }
            if (! Schema::hasColumn('leases', 'deposit_deduction_reason')) {
                $table->text('deposit_deduction_reason')->nullable()->after('deposit_refunded');
            }
            if (! Schema::hasColumn('leases', 'deposit_returned_at')) {
                $table->timestamp('deposit_returned_at')->nullable()->after('deposit_deduction_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_documents', function (Blueprint $table) {
            $table->dropColumn(['version', 'notes']);
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn([
                'deposit_status',
                'deposit_deductions',
                'deposit_refunded',
                'deposit_deduction_reason',
                'deposit_returned_at',
            ]);
        });
    }
};
