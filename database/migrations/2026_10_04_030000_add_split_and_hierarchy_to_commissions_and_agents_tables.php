<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->string('tier')->default('junior')->after('bio'); // junior, senior, principal
            $table->decimal('split_percentage', 5, 2)->default(60.00)->after('tier'); // e.g. 60.00%
            $table->string('license_number')->nullable()->after('split_percentage');
            $table->boolean('license_verified')->default(false)->after('license_number');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->string('split_type')->default('single_agent')->after('status'); // single_agent, co_brokered, referral
            $table->decimal('total_deal_commission', 14, 2)->nullable()->after('split_type');
            $table->foreignId('listing_agent_id')->nullable()->constrained('agents')->nullOnDelete()->after('total_deal_commission');
            $table->foreignId('selling_agent_id')->nullable()->constrained('agents')->nullOnDelete()->after('listing_agent_id');
            $table->foreignId('referral_agent_id')->nullable()->constrained('agents')->nullOnDelete()->after('selling_agent_id');
            $table->decimal('listing_agent_amount', 14, 2)->nullable()->after('referral_agent_id');
            $table->decimal('selling_agent_amount', 14, 2)->nullable()->after('listing_agent_amount');
            $table->decimal('referral_amount', 14, 2)->nullable()->after('selling_agent_amount');
            $table->decimal('company_retained_amount', 14, 2)->nullable()->after('referral_amount');
            $table->string('agent_tier_applied')->nullable()->after('company_retained_amount');
            $table->json('split_breakdown')->nullable()->after('agent_tier_applied');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['listing_agent_id']);
            $table->dropForeign(['selling_agent_id']);
            $table->dropForeign(['referral_agent_id']);
            $table->dropColumn([
                'split_type',
                'total_deal_commission',
                'listing_agent_id',
                'selling_agent_id',
                'referral_agent_id',
                'listing_agent_amount',
                'selling_agent_amount',
                'referral_amount',
                'company_retained_amount',
                'agent_tier_applied',
                'split_breakdown',
            ]);
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn([
                'tier',
                'split_percentage',
                'license_number',
                'license_verified',
            ]);
        });
    }
};
