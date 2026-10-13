<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Assign property to users (for hotel/guesthouse receptionists)
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'assigned_property_id')) {
                $table->foreignId('assigned_property_id')->nullable()->after('role')->constrained('properties')->nullOnDelete();
            }
        });

        // 2. Add front desk check-in fields to bookings
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'room_number')) {
                $table->string('room_number')->nullable()->after('listing_id');
            }
            if (! Schema::hasColumn('bookings', 'guest_id_number')) {
                $table->string('guest_id_number')->nullable()->after('guest_phone');
            }
            if (! Schema::hasColumn('bookings', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('check_in_date');
            }
            if (! Schema::hasColumn('bookings', 'checked_out_at')) {
                $table->timestamp('checked_out_at')->nullable()->after('check_out_date');
            }
        });

        // 3. Front Desk Cash & Payment Shift Reconciliation table (communicates with Finance Officer)
        Schema::create('front_desk_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('shift_reference')->unique(); // e.g. SFT-20261006-8941
            $table->foreignId('receptionist_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->date('shift_date');
            $table->string('shift_type')->default('day'); // morning, day, night
            $table->decimal('cash_collected', 14, 2)->default(0);
            $table->decimal('telebirr_collected', 14, 2)->default(0);
            $table->decimal('cbe_collected', 14, 2)->default(0);
            $table->decimal('card_pos_collected', 14, 2)->default(0);
            $table->decimal('total_collected', 14, 2)->default(0);
            $table->unsignedInteger('checkins_count')->default(0);
            $table->unsignedInteger('checkouts_count')->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('submitted'); // submitted, verified_by_finance, discrepancy_flagged
            $table->foreignId('finance_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finance_verified_at')->nullable();
            $table->text('finance_notes')->nullable();
            $table->timestamps();

            $table->index(['shift_date', 'status']);
            $table->index('receptionist_user_id');
        });

        // 4. Guest Service Requests table (communication between receptionist & guest/client)
        Schema::create('guest_service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('room_number')->nullable();
            $table->string('guest_name');
            $table->string('service_type'); // room_cleaning, extra_towels, airport_transfer, wake_up_call, maintenance, inquiry, other
            $table->string('priority')->default('normal'); // normal, urgent
            $table->text('description');
            $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('response_notes')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_service_requests');
        Schema::dropIfExists('front_desk_shifts');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['room_number', 'guest_id_number', 'checked_in_at', 'checked_out_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['assigned_property_id']);
            $table->dropColumn('assigned_property_id');
        });
    }
};
