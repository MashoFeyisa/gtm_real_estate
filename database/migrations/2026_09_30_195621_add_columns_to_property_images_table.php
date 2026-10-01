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
        Schema::table('property_images', function (Blueprint $table) {
            $table->foreignId('property_id')->after('id')->constrained()->cascadeOnDelete();
            $table->string('image_path')->after('property_id');
            $table->unsignedInteger('sort_order')->default(0)->after('image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_images', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropColumn(['property_id', 'image_path', 'sort_order']);
        });
    }
};
