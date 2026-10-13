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
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->string('lease_code')->unique();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('landlord_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tenant_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Tenant KYC & contact details
            $table->string('tenant_name');
            $table->string('tenant_email');
            $table->string('tenant_phone');
            $table->string('tenant_id_type')->default('kebele_id'); // kebele_id, passport, national_id
            $table->string('tenant_id_number')->nullable();
            $table->string('tenant_occupation')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // Lease duration & dates
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months')->default(12);

            // Financial specifications
            $table->decimal('monthly_rent', 15, 2);
            $table->decimal('security_deposit', 15, 2)->default(0);
            $table->string('payment_frequency')->default('monthly'); // monthly, quarterly, semi_annually, annually
            $table->integer('grace_period_days')->default(5);
            $table->decimal('late_fee_percentage', 5, 2)->default(0);

            // Agreement status & details
            $table->string('status')->default('active'); // draft, active, pending_renewal, expired, terminated
            $table->text('terms_and_conditions')->nullable();
            $table->string('agreement_document_path')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        Schema::create('rent_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained('leases')->cascadeOnDelete();
            $table->integer('installment_number');
            $table->string('period_label');
            $table->date('due_date');
            $table->decimal('amount_due', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, paid, overdue, partial
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_schedules');
        Schema::dropIfExists('leases');
    }
};
