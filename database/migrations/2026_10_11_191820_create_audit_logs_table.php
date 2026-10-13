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
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('actor_name')->nullable();
                $table->string('action'); // e.g., role.create, permission.assign, dashboard.toggle, user.status, sensitive_action
                $table->string('entity_type')->nullable(); // e.g., Role, User, Permission
                $table->string('entity_id')->nullable();
                $table->json('old_value')->nullable();
                $table->json('new_value')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('device', 255)->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->index(['action', 'created_at']);
                $table->index(['entity_type', 'entity_id']);
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
