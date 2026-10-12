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
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('city')->default('Addis Ababa')->index();
            $table->string('location_address')->nullable();
            $table->string('category')->default('cultural')->index();
            $table->text('description');
            $table->decimal('price_etb', 12, 2);
            $table->decimal('price_usd', 10, 2)->nullable();
            $table->decimal('duration_hours', 4, 1)->default(3.0);
            $table->integer('max_participants')->default(10);
            $table->json('languages')->nullable();
            $table->json('included_items')->nullable();
            $table->string('meeting_point')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('cover_image')->nullable();
            $table->string('status')->default('published')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('experience_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('booking_reference')->unique();
            $table->date('scheduled_date');
            $table->string('scheduled_time')->default('10:00');
            $table->integer('participants_count')->default(1);
            $table->decimal('total_price', 12, 2);
            $table->string('currency', 10)->default('ETB');
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('payment_status')->default('unpaid')->index();
            $table->string('payment_method')->default('telebirr');
            $table->string('status')->default('pending')->index();
            $table->text('special_requests')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experience_bookings');
        Schema::dropIfExists('experiences');
    }
};
