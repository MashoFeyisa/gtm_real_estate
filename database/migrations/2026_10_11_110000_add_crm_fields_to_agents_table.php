<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->json('service_locations')->nullable()->after('bio');
            $table->json('languages')->nullable()->after('service_locations');
            $table->string('phone_privacy', 30)->default('show')->after('languages'); // show, hide, messaging_only
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn(['service_locations', 'languages', 'phone_privacy']);
        });
    }
};
