<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('subcity')->nullable()->after('city')->index();
            $table->string('woreda')->nullable()->after('subcity');
            $table->string('neighborhood')->nullable()->after('woreda')->index();
            $table->json('nearby_amenities')->nullable()->after('longitude');
            $table->unsignedTinyInteger('walk_score')->nullable()->after('nearby_amenities');
            $table->unsignedTinyInteger('transit_score')->nullable()->after('walk_score');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['subcity']);
            $table->dropIndex(['neighborhood']);
            $table->dropColumn([
                'subcity',
                'woreda',
                'neighborhood',
                'nearby_amenities',
                'walk_score',
                'transit_score',
            ]);
        });
    }
};
