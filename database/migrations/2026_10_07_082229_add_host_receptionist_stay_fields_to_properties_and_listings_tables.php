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
            if (! Schema::hasColumn('properties', 'host_id')) {
                $table->foreignId('host_id')->nullable()->after('agent_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('properties', 'house_rules')) {
                $table->text('house_rules')->nullable()->after('description');
            }
            if (! Schema::hasColumn('properties', 'check_in_time')) {
                $table->string('check_in_time')->nullable()->default('14:00')->after('house_rules');
            }
            if (! Schema::hasColumn('properties', 'check_out_time')) {
                $table->string('check_out_time')->nullable()->default('11:00')->after('check_in_time');
            }
            if (! Schema::hasColumn('properties', 'cleaning_fee')) {
                $table->decimal('cleaning_fee', 10, 2)->default(0)->after('price');
            }
            if (! Schema::hasColumn('properties', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('is_active');
            }
            if (! Schema::hasColumn('properties', 'available_from')) {
                $table->date('available_from')->nullable()->after('is_available');
            }
            if (! Schema::hasColumn('properties', 'available_to')) {
                $table->date('available_to')->nullable()->after('available_from');
            }
        });

        Schema::table('listings', function (Blueprint $table) {
            if (! Schema::hasColumn('listings', 'house_rules')) {
                $table->text('house_rules')->nullable()->after('description');
            }
            if (! Schema::hasColumn('listings', 'available_from')) {
                $table->date('available_from')->nullable()->after('maximum_stay');
            }
            if (! Schema::hasColumn('listings', 'available_to')) {
                $table->date('available_to')->nullable()->after('available_from');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['host_id']);
            $table->dropColumn([
                'host_id',
                'house_rules',
                'check_in_time',
                'check_out_time',
                'cleaning_fee',
                'is_available',
                'available_from',
                'available_to',
            ]);
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'house_rules',
                'available_from',
                'available_to',
            ]);
        });
    }
};
