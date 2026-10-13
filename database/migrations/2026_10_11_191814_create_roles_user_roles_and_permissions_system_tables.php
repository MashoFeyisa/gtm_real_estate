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
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'role_id']);
            });
        }

        if (! Schema::hasTable('user_permissions')) {
            Schema::create('user_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
                $table->boolean('granted')->default(true);
                $table->timestamps();

                $table->unique(['user_id', 'permission_id']);
            });
        }

        if (Schema::hasTable('role_permissions') && ! Schema::hasColumn('role_permissions', 'role_id')) {
            Schema::table('role_permissions', function (Blueprint $table) {
                $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->cascadeOnDelete();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('role'); // active, suspended, pending
            }
            if (! Schema::hasColumn('users', 'dashboard_access_enabled')) {
                $table->boolean('dashboard_access_enabled')->default(true)->after('status');
            }
            if (! Schema::hasColumn('users', 'media_access_enabled')) {
                $table->boolean('media_access_enabled')->default(true)->after('dashboard_access_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'media_access_enabled')) {
                $table->dropColumn('media_access_enabled');
            }
            if (Schema::hasColumn('users', 'dashboard_access_enabled')) {
                $table->dropColumn('dashboard_access_enabled');
            }
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
        });

        if (Schema::hasTable('role_permissions') && Schema::hasColumn('role_permissions', 'role_id')) {
            Schema::table('role_permissions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('role_id');
            });
        }

        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
};
