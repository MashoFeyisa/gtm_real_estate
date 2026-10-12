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
        Schema::create('subcity_boundaries', function (Blueprint $table) {
            $table->id();
            $table->string('city')->default('Addis Ababa')->index();
            $table->string('name')->index();
            $table->string('amharic_name')->nullable();
            $table->string('om_name')->nullable();
            $table->decimal('center_lat', 10, 7);
            $table->decimal('center_lng', 10, 7);
            $table->json('coordinates');
            $table->integer('properties_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcity_boundaries');
    }
};
