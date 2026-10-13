<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('property_code')->nullable()->unique()->after('id');
            $table->unsignedInteger('building_area')->nullable()->after('area');
            $table->unsignedInteger('floors')->default(1)->after('bathrooms');
            $table->unsignedInteger('parking_spaces')->default(1)->after('floors');
            $table->unsignedInteger('balconies')->nullable()->default(0)->after('parking_spaces');
            $table->unsignedInteger('year_built')->nullable()->after('balconies');
            $table->string('condition')->default('excellent')->after('year_built');
            $table->boolean('has_electricity')->default(true)->after('condition');
            $table->boolean('has_water')->default(true)->after('has_electricity');
            $table->boolean('has_generator')->default(false)->after('has_water');
            $table->boolean('has_water_tank')->default(true)->after('has_generator');
            $table->boolean('has_solar')->default(false)->after('has_water_tank');
            $table->boolean('has_security')->default(true)->after('has_solar');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'property_code',
                'building_area',
                'floors',
                'parking_spaces',
                'balconies',
                'year_built',
                'condition',
                'has_electricity',
                'has_water',
                'has_generator',
                'has_water_tank',
                'has_solar',
                'has_security',
                'latitude',
                'longitude',
            ]);
        });
    }
};
