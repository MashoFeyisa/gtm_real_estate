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
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('category')->nullable()->after('slug');
            $table->string('tags')->nullable()->after('category');
            $table->string('author_name')->nullable()->after('tags');
            $table->string('status')->default('draft')->after('type');
            $table->text('excerpt')->nullable()->after('content');
            $table->string('seo_title')->nullable()->after('excerpt');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('social_image_path')->nullable()->after('image_path');
            $table->timestamp('published_at')->nullable()->after('social_image_path');
            $table->timestamp('scheduled_for')->nullable()->after('published_at');
            $table->string('related_post_ids')->nullable()->after('scheduled_for');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'tags',
                'author_name',
                'status',
                'excerpt',
                'seo_title',
                'seo_description',
                'social_image_path',
                'published_at',
                'scheduled_for',
                'related_post_ids',
            ]);
        });
    }
};
