<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Property Viewing Appointments (SRS §71, §72)
        Schema::create('property_viewings', function (Blueprint $table) {
            $table->id();
            $table->string('viewing_reference')->unique();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->string('buyer_phone');
            $table->date('viewing_date');
            $table->string('viewing_time'); // morning_09_11, afternoon_14_16, evening_17_19
            $table->string('viewing_type')->default('in_person'); // in_person, virtual_tour
            $table->string('status')->default('requested'); // requested, confirmed, rescheduled, completed, cancelled, no_show (SRS §72)
            $table->text('buyer_notes')->nullable();
            $table->text('agent_notes')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index(['agent_id', 'viewing_date']);
        });

        // 2. Formal Property Offers (SRS §76: Offer Management)
        Schema::create('property_offers', function (Blueprint $table) {
            $table->id();
            $table->string('offer_reference')->unique();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->string('buyer_phone');
            $table->string('offer_type')->default('purchase'); // purchase, rental
            $table->decimal('original_price', 14, 2);
            $table->decimal('offered_price', 14, 2);
            $table->string('currency', 3)->default('ETB');
            $table->string('financing_type')->default('cash'); // cash, bank_loan, diaspora_remittance, installments
            $table->decimal('deposit_proposed', 14, 2)->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->string('status')->default('submitted'); // submitted, under_review, countered, accepted, rejected, expired, withdrawn (SRS §76)
            $table->decimal('current_counter_amount', 14, 2)->nullable();
            $table->string('latest_counter_by')->nullable(); // buyer, seller_agent
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index(['agent_id', 'status']);
        });

        // 3. Multi-Round Negotiation Events & Counteroffers (SRS §77)
        Schema::create('offer_negotiations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_offer_id')->constrained('property_offers')->cascadeOnDelete();
            $table->string('sender_type'); // buyer, seller_agent, admin
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('ETB');
            $table->text('message')->nullable();
            $table->json('terms')->nullable();
            $table->timestamps();

            $table->index(['property_offer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_negotiations');
        Schema::dropIfExists('property_offers');
        Schema::dropIfExists('property_viewings');
    }
};
