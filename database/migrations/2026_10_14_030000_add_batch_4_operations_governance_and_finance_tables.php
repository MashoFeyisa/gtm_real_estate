<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Batch 4 Operations, Governance, Trust & Finance roles.
     */
    public function up(): void
    {
        // 1. Users table KYC & Warning enhancements
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_card_path')->nullable()->after('portal_bg_path');
            $table->string('id_card_type')->nullable()->after('id_card_path'); // national_id, passport, kebele_id, driving_license
            $table->string('id_verification_status')->default('unverified')->after('id_card_type'); // unverified, pending, approved, rejected
            $table->text('id_rejection_reason')->nullable()->after('id_verification_status');
            $table->timestamp('id_verified_at')->nullable()->after('id_rejection_reason');
            $table->boolean('is_warned')->default(false)->after('id_verified_at');
            $table->text('warning_notes')->nullable()->after('is_warned');
        });

        // 2. Property reports fraud & action tracking
        Schema::table('property_reports', function (Blueprint $table) {
            $table->boolean('is_fraud_flagged')->default(false)->after('status');
            $table->string('action_taken')->nullable()->after('is_fraud_flagged'); // listing_unpublished, user_warned, fraud_flagged, dismissed
        });

        // 3. Payments refund tracking
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('refunded_amount', 15, 2)->default(0.00)->after('net_amount');
            $table->text('refund_reason')->nullable()->after('refunded_amount');
        });

        // 4. Invoices table
        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
                $table->string('title');
                $table->decimal('subtotal', 15, 2);
                $table->decimal('vat_rate', 5, 2)->default(15.00); // 15% VAT
                $table->decimal('vat_amount', 15, 2)->default(0.00);
                $table->decimal('withholding_tax_rate', 5, 2)->default(2.00); // 2% withholding
                $table->decimal('withholding_tax_amount', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2);
                $table->string('status')->default('unpaid'); // unpaid, paid, cancelled, overdue
                $table->date('due_date')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->text('notes')->nullable();
                $table->json('items')->nullable();
                $table->timestamps();
            });
        }

        // 5. Payout requests table
        if (! Schema::hasTable('payout_requests')) {
            Schema::create('payout_requests', function (Blueprint $table) {
                $table->id();
                $table->string('payout_code')->unique();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role')->default('host'); // host, seller, agent, professional
                $table->decimal('gross_amount', 15, 2);
                $table->decimal('withholding_tax', 15, 2)->default(0.00);
                $table->decimal('net_amount', 15, 2);
                $table->string('payment_method')->default('bank_transfer'); // bank_transfer, telebirr, cbe_birr
                $table->string('bank_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('status')->default('pending'); // pending, approved, rejected, disbursed
                $table->text('rejection_reason')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('processed_at')->nullable();
                $table->string('transaction_reference')->nullable();
                $table->timestamps();
            });
        }

        // 6. User disputes table
        if (! Schema::hasTable('user_disputes')) {
            Schema::create('user_disputes', function (Blueprint $table) {
                $table->id();
                $table->string('dispute_code')->unique();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('respondent_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
                $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
                $table->string('title');
                $table->text('description');
                $table->string('stage')->default('dispute'); // dispute, evidence, review, resolution, closed
                $table->text('evidence_notes')->nullable();
                $table->json('evidence_files')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_disputes');
        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('invoices');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['refunded_amount', 'refund_reason']);
        });

        Schema::table('property_reports', function (Blueprint $table) {
            $table->dropColumn(['is_fraud_flagged', 'action_taken']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'id_card_path',
                'id_card_type',
                'id_verification_status',
                'id_rejection_reason',
                'id_verified_at',
                'is_warned',
                'warning_notes',
            ]);
        });
    }
};
