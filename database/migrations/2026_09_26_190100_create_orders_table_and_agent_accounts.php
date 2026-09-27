<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('password')->nullable()->after('user_id');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->decimal('offer_amount', 14, 2)->nullable();
            $table->string('status')->default('pending');
            $table->text('agent_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');

        Schema::table('agents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('password');
        });
    }
};
