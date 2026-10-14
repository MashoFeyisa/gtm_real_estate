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
        Schema::table('maintenance_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('maintenance_tickets', 'estimated_cost')) {
                $table->decimal('estimated_cost', 14, 2)->nullable()->default(0)->after('priority');
            }
            if (! Schema::hasColumn('maintenance_tickets', 'actual_cost')) {
                $table->decimal('actual_cost', 14, 2)->nullable()->default(0)->after('estimated_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->dropColumn(['estimated_cost', 'actual_cost']);
        });
    }
};
