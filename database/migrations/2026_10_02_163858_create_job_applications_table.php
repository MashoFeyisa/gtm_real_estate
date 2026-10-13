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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->nullable()->constrained('blog_posts')->nullOnDelete();
            $table->string('job_title');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('current_location')->nullable();
            $table->string('experience_years')->nullable();
            $table->string('education_level')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('expected_salary')->nullable();
            $table->string('available_start_date')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('resume_path');
            $table->string('resume_original_name');
            $table->string('resume_mime_type')->nullable();
            $table->unsignedBigInteger('resume_size')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
