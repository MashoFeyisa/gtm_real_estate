<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique(); // e.g. STAY-AA-202610-8842
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedInteger('total_nights')->default(1);
            $table->unsignedInteger('guests_count')->default(1);
            $table->decimal('nightly_price', 14, 2);
            $table->decimal('accommodation_total', 14, 2);
            $table->decimal('cleaning_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->string('currency', 3)->default('ETB');
            $table->string('status')->default('confirmed'); // pending, confirmed, checked_in, checked_out, completed, cancelled
            $table->text('special_requests')->nullable();
            $table->text('host_notes')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'check_in_date', 'check_out_date']);
            $table->index(['status', 'check_in_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
