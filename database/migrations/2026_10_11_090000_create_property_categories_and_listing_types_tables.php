<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_categories', function (Blueprint $table) {
            $table->id();
            $table->string('group'); // Residential, Commercial, Industrial, Hospitality, Special
            $table->string('name');
            $table->string('slug')->index(); // names may repeat across groups (e.g. Hotel is both Commercial and Hospitality)
            $table->string('icon', 30)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group', 'is_active']);
        });

        Schema::create('listing_types', function (Blueprint $table) {
            $table->id();
            $table->string('mode'); // buy, rent, lease, stay, new_projects
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['mode', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_types');
        Schema::dropIfExists('property_categories');
    }
};
