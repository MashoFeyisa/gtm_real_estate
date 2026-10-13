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
        Schema::table('property_reviews', function (Blueprint $table) {
            $table->text('host_reply')->nullable()->after('review_body');
            $table->timestamp('host_replied_at')->nullable()->after('host_reply');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_reviews', function (Blueprint $table) {
            $table->dropColumn(['host_reply', 'host_replied_at']);
        });
    }
};
