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
        // 1. Maintenance & Housekeeping Requests Engine
        Schema::create('maintenance_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ticket_number')->unique();
            $table->string('title');
            $table->string('category')->default('other'); // plumbing, electrical, internet, appliance, structural, cleaning, other
            $table->string('priority')->default('medium'); // low, medium, high, emergency
            $table->string('status')->default('open'); // open, assigned, in_progress, completed, closed
            $table->text('description');
            $table->string('location_notes')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });

        // 2. Two-Way Verified Property & Stay Reviews
        Schema::create('property_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->unsignedTinyInteger('cleanliness_rating')->nullable();
            $table->unsignedTinyInteger('accuracy_rating')->nullable();
            $table->unsignedTinyInteger('communication_rating')->nullable();
            $table->unsignedTinyInteger('location_rating')->nullable();
            $table->unsignedTinyInteger('value_rating')->nullable();
            $table->string('review_title')->nullable();
            $table->text('review_body');
            $table->boolean('is_verified_stay')->default(false);
            $table->string('status')->default('published'); // published, pending, hidden
            $table->timestamps();
        });

        // 3. Trust & Safety / Scam & Misleading Listing Reports
        Schema::create('property_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('report_number')->unique();
            $table->string('reason'); // scam, fake_property, duplicate, wrong_price, unavailable, misleading, suspicious_documents, inappropriate_content
            $table->text('description');
            $table->string('contact_email')->nullable();
            $table->string('status')->default('pending'); // pending, under_investigation, resolved, dismissed
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });

        // 4. Saved Property Collections & Portfolios
        Schema::create('saved_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('collection_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('saved_collections')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['collection_id', 'property_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_properties');
        Schema::dropIfExists('saved_collections');
        Schema::dropIfExists('property_reports');
        Schema::dropIfExists('property_reviews');
        Schema::dropIfExists('maintenance_tickets');
    }
};
