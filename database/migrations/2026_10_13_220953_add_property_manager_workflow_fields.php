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
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('user_id')->constrained('properties')->nullOnDelete();
            }
            if (! Schema::hasColumn('properties', 'unit_number')) {
                $table->string('unit_number')->nullable()->after('title');
            }
        });

        if (! Schema::hasTable('property_inspections')) {
            Schema::create('property_inspections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lease_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('inspector_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('inspection_code')->unique();
                $table->string('inspection_type')->default('move_in'); // move_in, move_out, routine, emergency
                $table->date('inspection_date');
                $table->string('overall_condition')->default('good'); // excellent, good, fair, poor, damaged
                $table->json('checklist')->nullable();
                $table->text('notes')->nullable();
                $table->json('photo_paths')->nullable();
                $table->string('status')->default('completed'); // draft, completed, signed
                $table->timestamps();

                $table->index(['property_id', 'inspection_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_inspections');

        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'unit_number']);
        });
    }
};
