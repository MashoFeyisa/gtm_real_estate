<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('workers');
    }

    public function down(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('department')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->timestamp('recorded_at');
            $table->time('check_in_time')->nullable();
            $table->timestamps();
        });
    }
};
