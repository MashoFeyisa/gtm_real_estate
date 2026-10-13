<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Real Estate Development Projects Table (SRS §89, §90, §156)
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('developer_name');
            $table->string('developer_phone')->nullable();
            $table->string('developer_email')->nullable();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->string('city')->default('Addis Ababa');
            $table->string('subcity')->nullable();
            $table->string('address')->nullable();
            $table->string('project_type')->default('mixed_use'); // residential, commercial, mixed_use, luxury_villas, apartments
            $table->string('construction_status')->default('under_construction'); // planning, pre_construction, under_construction, near_completion, completed, delivered, cancelled
            $table->unsignedTinyInteger('overall_completion_percentage')->default(0); // 0-100%
            $table->string('expected_completion_date')->nullable(); // e.g. Q4 2027
            $table->decimal('land_area_sqm', 12, 2)->nullable();
            $table->unsignedSmallInteger('total_buildings_count')->default(1);
            $table->unsignedSmallInteger('total_units_count')->default(0);
            $table->decimal('starting_price', 14, 2)->default(0);
            $table->string('currency', 3)->default('ETB');
            $table->longText('description')->nullable();
            $table->json('facilities')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('published'); // draft, published, completed, archived
            $table->timestamps();

            $table->index(['city', 'construction_status']);
            $table->index(['project_type', 'status']);
        });

        // 2. Project Buildings / Towers (SRS §89: Project -> Buildings -> Units)
        Schema::create('project_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('total_floors')->default('G+12');
            $table->unsignedSmallInteger('units_count')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Project Units Inventory (SRS §91, §92, §157)
        Schema::create('project_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('building_id')->nullable()->constrained('project_buildings')->nullOnDelete();
            $table->string('unit_number'); // e.g. A-301, B-1204
            $table->string('floor_number')->default('1');
            $table->string('unit_type')->default('2_bedroom'); // studio, 1_bedroom, 2_bedroom, 3_bedroom, 4_bedroom, penthouse, duplex, retail_shop
            $table->unsignedTinyInteger('bedrooms')->default(2);
            $table->unsignedTinyInteger('bathrooms')->default(2);
            $table->decimal('area_sqm', 8, 2);
            $table->decimal('price', 14, 2);
            $table->string('currency', 3)->default('ETB');
            $table->string('orientation')->nullable(); // e.g. Bole View, Sunrise East
            $table->unsignedTinyInteger('parking_spaces')->default(1);
            $table->string('status')->default('available'); // available, reserved, contracted, sold, blocked
            $table->string('floor_plan_image')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['bedrooms', 'price']);
        });

        // 4. Construction Progress Milestones (SRS §93: Foundation, Structure, Masonry, Finishing, Overall)
        Schema::create('construction_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('milestone_name');
            $table->unsignedTinyInteger('percentage')->default(0); // 0-100%
            $table->string('target_date')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->unsignedSmallInteger('order_index')->default(1);
            $table->timestamps();
        });

        // 5. Developer Milestone Payment Plans (SRS §94: 10% Reservation, 20% Foundation, etc.)
        Schema::create('developer_payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('installment_name');
            $table->decimal('percentage', 5, 2);
            $table->string('milestone_trigger')->nullable();
            $table->unsignedSmallInteger('order_index')->default(1);
            $table->timestamps();
        });

        // 6. Unit Reservations & Inquiries Workflow (SRS §95: Sales Workflow)
        Schema::create('unit_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_reference')->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('project_units')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->string('buyer_phone');
            $table->string('status')->default('pending'); // pending, confirmed, contracted, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_reservations');
        Schema::dropIfExists('developer_payment_plans');
        Schema::dropIfExists('construction_milestones');
        Schema::dropIfExists('project_units');
        Schema::dropIfExists('project_buildings');
        Schema::dropIfExists('projects');
    }
};
