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
        Schema::create('corporate_tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('company_name');
            $table->string('tax_id')->nullable();
            $table->string('contact_person');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('industry')->default('General Corporate');
            $table->string('billing_address')->nullable();
            $table->string('city')->default('Addis Ababa');
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->string('status')->default('active'); // active, suspended, pending
            $table->timestamps();
        });

        Schema::create('corporate_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_tenant_id')->constrained('corporate_tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('employee_id_number');
            $table->string('email');
            $table->string('phone');
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        Schema::create('corporate_lease_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_tenant_id')->constrained('corporate_tenants')->cascadeOnDelete();
            $table->foreignId('lease_id')->nullable()->constrained('leases')->nullOnDelete();
            $table->foreignId('corporate_employee_id')->constrained('corporate_employees')->cascadeOnDelete();
            $table->string('unit_identifier')->nullable();
            $table->date('assigned_from');
            $table->date('assigned_until')->nullable();
            $table->decimal('monthly_rent_etb', 15, 2)->default(0);
            $table->string('status')->default('active'); // active, concluded, cancelled
            $table->timestamps();
        });

        Schema::create('company_white_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('company_name');
            $table->string('subdomain')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('brand_name');
            $table->string('logo_path')->nullable();
            $table->string('primary_color')->default('#0284c7');
            $table->string('secondary_color')->default('#0f172a');
            $table->string('support_phone')->nullable();
            $table->string('support_email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('developer_escrow_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('milestone_id')->constrained('construction_milestones')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount_etb', 15, 2);
            $table->string('status')->default('pending'); // pending, approved, disbursed, rejected
            $table->string('bank_reference')->nullable();
            $table->text('inspection_notes')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('developer_escrow_disbursements');
        Schema::dropIfExists('company_white_labels');
        Schema::dropIfExists('corporate_lease_assignments');
        Schema::dropIfExists('corporate_employees');
        Schema::dropIfExists('corporate_tenants');
    }
};
