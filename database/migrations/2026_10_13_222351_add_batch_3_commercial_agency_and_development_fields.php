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
        // 1. Agent direct clients table
        if (! Schema::hasTable('agent_clients')) {
            Schema::create('agent_clients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('lead_source')->default('portal'); // walk_in, portal, referral, social, direct
                $table->string('stage')->default('new_lead'); // new_lead, contacted, qualified, viewing, negotiation, offer, closed
                $table->decimal('budget', 15, 2)->nullable();
                $table->string('preferred_type')->nullable();
                $table->string('preferred_location')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Add lead_source to inquiries
        Schema::table('inquiries', function (Blueprint $table) {
            if (! Schema::hasColumn('inquiries', 'lead_source')) {
                $table->string('lead_source')->default('portal')->after('status');
            }
        });

        // 3. Add developer_user_id to projects
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'developer_user_id')) {
                $table->foreignId('developer_user_id')->nullable()->after('agent_id')->constrained('users')->nullOnDelete();
            }
        });

        // 4. Add photo_path to construction_milestones
        Schema::table('construction_milestones', function (Blueprint $table) {
            if (! Schema::hasColumn('construction_milestones', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('status');
            }
        });

        // 5. Add subscription fields to company_white_labels
        Schema::table('company_white_labels', function (Blueprint $table) {
            if (! Schema::hasColumn('company_white_labels', 'subscription_tier')) {
                $table->string('subscription_tier')->default('standard')->after('is_active');
            }
            if (! Schema::hasColumn('company_white_labels', 'subscription_expires_at')) {
                $table->timestamp('subscription_expires_at')->nullable()->after('subscription_tier');
            }
        });

        // 6. Add professional provider fields to users
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'profession')) {
                $table->string('profession')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'hourly_rate')) {
                $table->decimal('hourly_rate', 10, 2)->nullable()->after('profession');
            }
            if (! Schema::hasColumn('users', 'license_number')) {
                $table->string('license_number')->nullable()->after('hourly_rate');
            }
            if (! Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('license_number');
            }
            if (! Schema::hasColumn('users', 'portfolio_summary')) {
                $table->text('portfolio_summary')->nullable()->after('bio');
            }
        });

        // 7. Add quote fields to professional_service_requests
        Schema::table('professional_service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('professional_service_requests', 'quote_notes')) {
                $table->text('quote_notes')->nullable()->after('fee');
            }
            if (! Schema::hasColumn('professional_service_requests', 'estimated_days')) {
                $table->integer('estimated_days')->nullable()->after('quote_notes');
            }
        });

        // 8. Add payment plan & contract fields to unit_reservations
        Schema::table('unit_reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('unit_reservations', 'payment_plan_id')) {
                $table->foreignId('payment_plan_id')->nullable()->after('status')->constrained('developer_payment_plans')->nullOnDelete();
            }
            if (! Schema::hasColumn('unit_reservations', 'deposit_amount')) {
                $table->decimal('deposit_amount', 15, 2)->nullable()->after('payment_plan_id');
            }
            if (! Schema::hasColumn('unit_reservations', 'contract_date')) {
                $table->date('contract_date')->nullable()->after('deposit_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_clients');

        Schema::table('inquiries', function (Blueprint $table) {
            if (Schema::hasColumn('inquiries', 'lead_source')) {
                $table->dropColumn('lead_source');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'developer_user_id')) {
                $table->dropForeign(['developer_user_id']);
                $table->dropColumn('developer_user_id');
            }
        });

        Schema::table('construction_milestones', function (Blueprint $table) {
            if (Schema::hasColumn('construction_milestones', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
        });

        Schema::table('company_white_labels', function (Blueprint $table) {
            if (Schema::hasColumn('company_white_labels', 'subscription_expires_at')) {
                $table->dropColumn('subscription_expires_at');
            }
            if (Schema::hasColumn('company_white_labels', 'subscription_tier')) {
                $table->dropColumn('subscription_tier');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $cols = ['portfolio_summary', 'bio', 'license_number', 'hourly_rate', 'profession'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('professional_service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('professional_service_requests', 'estimated_days')) {
                $table->dropColumn('estimated_days');
            }
            if (Schema::hasColumn('professional_service_requests', 'quote_notes')) {
                $table->dropColumn('quote_notes');
            }
        });

        Schema::table('unit_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('unit_reservations', 'payment_plan_id')) {
                $table->dropForeign(['payment_plan_id']);
                $table->dropColumn('payment_plan_id');
            }
            if (Schema::hasColumn('unit_reservations', 'deposit_amount')) {
                $table->dropColumn('deposit_amount');
            }
            if (Schema::hasColumn('unit_reservations', 'contract_date')) {
                $table->dropColumn('contract_date');
            }
        });
    }
};
