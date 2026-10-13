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
        if (! Schema::hasTable('saved_searches')) {
            Schema::create('saved_searches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->json('criteria')->nullable();
                $table->string('alert_frequency')->default('daily'); // instant, daily, weekly, off
                $table->timestamp('last_notified_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cleaning_tasks')) {
            Schema::create('cleaning_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('cleaner_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
                $table->string('task_code')->unique();
                $table->string('room_number')->nullable();
                $table->string('status')->default('assigned'); // assigned, cleaning, inspection, ready
                $table->string('priority')->default('normal'); // low, normal, urgent
                $table->json('checklist')->nullable();
                $table->text('notes')->nullable();
                $table->date('scheduled_date')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('inspected_at')->nullable();
                $table->timestamp('ready_at')->nullable();
                $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('professional_service_requests')) {
            Schema::create('professional_service_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('professional_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
                $table->string('request_code')->unique();
                $table->string('service_type'); // valuer, lawyer, inspector, contractor
                $table->string('title');
                $table->text('description')->nullable();
                $table->dateTime('appointment_date')->nullable();
                $table->string('status')->default('requested'); // requested, accepted, in_progress, completed, declined, cancelled
                $table->decimal('fee', 14, 2)->default(0);
                $table->string('payment_status')->default('pending');
                $table->string('report_file_path')->nullable();
                $table->string('report_title')->nullable();
                $table->text('findings')->nullable();
                $table->tinyInteger('client_rating')->nullable();
                $table->text('client_review')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('investor_portfolios')) {
            Schema::create('investor_portfolios', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
                $table->string('asset_name');
                $table->string('asset_type')->default('residential');
                $table->string('location')->nullable();
                $table->decimal('purchase_price', 14, 2);
                $table->date('purchase_date')->nullable();
                $table->decimal('current_valuation', 14, 2);
                $table->decimal('monthly_rental_income', 14, 2)->default(0);
                $table->decimal('monthly_expenses', 14, 2)->default(0);
                $table->decimal('occupancy_rate', 5, 2)->default(100);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_portfolios');
        Schema::dropIfExists('professional_service_requests');
        Schema::dropIfExists('cleaning_tasks');
        Schema::dropIfExists('saved_searches');
    }
};
