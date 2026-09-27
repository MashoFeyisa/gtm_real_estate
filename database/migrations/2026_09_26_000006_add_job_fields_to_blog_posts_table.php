<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->rebuildTypeColumnForJobs();

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('job_location')->nullable();
            $table->string('job_type')->nullable();
            $table->string('salary_range')->nullable();
            $table->text('requirements')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('education_level')->nullable();
            $table->string('apply_link')->nullable();
            $table->date('application_deadline')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'job_location',
                'job_type',
                'salary_range',
                'requirements',
                'experience_level',
                'education_level',
                'apply_link',
                'application_deadline',
            ]);
        });
    }

    /**
     * Older installs carry a check constraint on the type column that excludes
     * the 'job' value, so rebuild the column with the full list of types.
     */
    protected function rebuildTypeColumnForJobs(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE blog_posts MODIFY type ENUM('blog', 'news', 'listing', 'project', 'job') NOT NULL");

            return;
        }

        $createSql = DB::selectOne(
            "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'blog_posts'"
        )?->sql;

        if (! $createSql) {
            return;
        }

        $repairedSql = (string) preg_replace(
            '/check\s*\(\s*"type"\s+in\s+\([^)]*\)\s*\)/i',
            "check (\"type\" in ('blog', 'news', 'listing', 'project', 'job'))",
            $createSql
        );

        if ($repairedSql === $createSql) {
            return;
        }

        $indexSqls = collect(DB::select(
            "SELECT sql FROM sqlite_master WHERE type = 'index' AND tbl_name = 'blog_posts' AND sql IS NOT NULL"
        ))->pluck('sql');

        $rebuiltSql = (string) preg_replace(
            '/CREATE TABLE\s+"?blog_posts"?\s*\(/i',
            'CREATE TABLE "blog_posts_rebuilt" (',
            $repairedSql,
            1
        );

        DB::statement($rebuiltSql);
        DB::statement('INSERT INTO "blog_posts_rebuilt" SELECT * FROM "blog_posts"');
        DB::statement('DROP TABLE "blog_posts"');
        DB::statement('ALTER TABLE "blog_posts_rebuilt" RENAME TO "blog_posts"');

        $indexSqls->each(fn (string $sql) => DB::statement($sql));
    }
};
