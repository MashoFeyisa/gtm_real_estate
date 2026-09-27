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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('submitted_to_admin_at')->nullable()->after('agreed_at');
            $table->timestamp('admin_viewed_at')->nullable()->after('submitted_to_admin_at');
            $table->string('admin_status')->nullable()->default('pending')->after('admin_viewed_at');
            $table->text('admin_note')->nullable()->after('admin_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'submitted_to_admin_at',
                'admin_viewed_at',
                'admin_status',
                'admin_note',
            ]);
        });
    }
};
