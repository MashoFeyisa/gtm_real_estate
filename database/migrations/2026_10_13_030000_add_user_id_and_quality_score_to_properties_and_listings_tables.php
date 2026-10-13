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
            if (! Schema::hasColumn('properties', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('listings', function (Blueprint $table) {
            if (! Schema::hasColumn('listings', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('property_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('listings', 'quality_score')) {
                $table->unsignedTinyInteger('quality_score')->default(0)->after('status');
            }
        });

        if (! Schema::hasTable('listing_price_histories')) {
            Schema::create('listing_price_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
                $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
                $table->decimal('old_price', 14, 2);
                $table->decimal('new_price', 14, 2);
                $table->string('currency', 10)->default('ETB');
                $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_price_histories');

        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'quality_score')) {
                $table->dropColumn('quality_score');
            }
            if (Schema::hasColumn('listings', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
