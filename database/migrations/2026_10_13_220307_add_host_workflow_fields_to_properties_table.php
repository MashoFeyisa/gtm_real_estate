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
            if (! Schema::hasColumn('properties', 'house_manual')) {
                $table->text('house_manual')->nullable()->after('house_rules');
            }
            if (! Schema::hasColumn('properties', 'check_in_instructions')) {
                $table->text('check_in_instructions')->nullable()->after('house_manual');
            }
            if (! Schema::hasColumn('properties', 'weekend_price')) {
                $table->decimal('weekend_price', 14, 2)->nullable()->after('price');
            }
            if (! Schema::hasColumn('properties', 'seasonal_price')) {
                $table->decimal('seasonal_price', 14, 2)->nullable()->after('weekend_price');
            }
            if (! Schema::hasColumn('properties', 'minimum_stay')) {
                $table->unsignedSmallInteger('minimum_stay')->default(1)->after('seasonal_price');
            }
            if (! Schema::hasColumn('properties', 'instant_booking_enabled')) {
                $table->boolean('instant_booking_enabled')->default(true)->after('minimum_stay');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'house_manual',
                'check_in_instructions',
                'weekend_price',
                'seasonal_price',
                'minimum_stay',
                'instant_booking_enabled',
            ]);
        });
    }
};
