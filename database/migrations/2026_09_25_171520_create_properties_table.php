<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('bedrooms')->default(0);
            $table->unsignedInteger('bathrooms')->default(0);
            $table->unsignedInteger('area')->default(0);
            $table->string('type')->default('sale');
            $table->string('status')->default('available');
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->boolean('featured')->default(false);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
