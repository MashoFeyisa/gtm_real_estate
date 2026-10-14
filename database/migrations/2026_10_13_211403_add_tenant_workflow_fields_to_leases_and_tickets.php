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
        if (Schema::hasTable('leases')) {
            Schema::table('leases', function (Blueprint $table) {
                if (! Schema::hasColumn('leases', 'signed_at')) {
                    $table->timestamp('signed_at')->nullable()->after('status');
                }
                if (! Schema::hasColumn('leases', 'tenant_signature')) {
                    $table->string('tenant_signature')->nullable()->after('signed_at');
                }
            });
        }

        if (Schema::hasTable('maintenance_tickets')) {
            Schema::table('maintenance_tickets', function (Blueprint $table) {
                if (! Schema::hasColumn('maintenance_tickets', 'photo_path')) {
                    $table->string('photo_path')->nullable()->after('description');
                }
            });
        }

        if (Schema::hasTable('property_offers')) {
            Schema::table('property_offers', function (Blueprint $table) {
                if (! Schema::hasColumn('property_offers', 'supporting_document_path')) {
                    $table->string('supporting_document_path')->nullable()->after('message');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('leases')) {
            Schema::table('leases', function (Blueprint $table) {
                if (Schema::hasColumn('leases', 'signed_at')) {
                    $table->dropColumn('signed_at');
                }
                if (Schema::hasColumn('leases', 'tenant_signature')) {
                    $table->dropColumn('tenant_signature');
                }
            });
        }

        if (Schema::hasTable('maintenance_tickets')) {
            Schema::table('maintenance_tickets', function (Blueprint $table) {
                if (Schema::hasColumn('maintenance_tickets', 'photo_path')) {
                    $table->dropColumn('photo_path');
                }
            });
        }

        if (Schema::hasTable('property_offers')) {
            Schema::table('property_offers', function (Blueprint $table) {
                if (Schema::hasColumn('property_offers', 'supporting_document_path')) {
                    $table->dropColumn('supporting_document_path');
                }
            });
        }
    }
};
