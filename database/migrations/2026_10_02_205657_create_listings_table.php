<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('listing_code')->unique();
            $table->string('listing_type')->default('sale'); // sale, rent, lease, stay, investment
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->string('currency', 3)->default('ETB');
            $table->string('pricing_period')->default('total'); // total, monthly, nightly, yearly
            $table->decimal('deposit_amount', 14, 2)->nullable()->default(0);
            $table->decimal('cleaning_fee', 10, 2)->nullable()->default(0);
            $table->decimal('service_fee', 10, 2)->nullable()->default(0);
            $table->unsignedSmallInteger('minimum_stay')->nullable()->default(1);
            $table->unsignedSmallInteger('maximum_stay')->nullable();
            $table->string('status')->default('published'); // draft, pending_review, approved, published, paused, expired, archived
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index(['listing_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
