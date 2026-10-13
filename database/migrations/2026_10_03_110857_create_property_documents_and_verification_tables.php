<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Property Verification and Legal Document Management (SRS §22–§29, §177).
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedTinyInteger('verification_level')->default(0)->after('status')->comment('Level 0 (Unverified) to Level 5 (Transaction Ready) per SRS §22');
            $table->string('verification_status', 50)->default('unverified')->after('verification_level')->comment('unverified, pending, verified, rejected');
            $table->json('verified_badges')->nullable()->after('verification_status')->comment('List of verified trust badges per SRS §23');
            $table->string('title_deed_number', 100)->nullable()->after('verified_badges')->comment('Ethiopian Title Deed / Carta number AA-BOL-...');
            $table->string('cadastre_code', 100)->nullable()->after('title_deed_number')->comment('Municipal Cadastre plot reference');
            $table->text('verification_notes')->nullable()->after('cadastre_code');
            $table->timestamp('verified_at')->nullable()->after('verification_notes');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
        });

        Schema::create('property_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type', 60)->comment('title_deed_carta, site_plan_mapa, building_permit, ownership_certificate, tax_clearance, power_of_attorney, seller_id, other');
            $table->string('title');
            $table->string('document_number', 100)->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('access_level', 50)->default('confidential')->comment('confidential, verification_only, transaction_participants, public per SRS §28');
            $table->string('status', 40)->default('pending')->comment('pending, verified, rejected, superseded');
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['property_id', 'document_type']);
            $table->index(['status', 'access_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_documents');

        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'verification_level',
                'verification_status',
                'verified_badges',
                'title_deed_number',
                'cadastre_code',
                'verification_notes',
                'verified_at',
                'verified_by',
            ]);
        });
    }
};
