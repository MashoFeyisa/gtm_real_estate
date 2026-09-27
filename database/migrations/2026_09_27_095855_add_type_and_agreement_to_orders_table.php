<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('type', 20)->default('sale')->after('property_id');
            $table->date('lease_start')->nullable()->after('offer_amount');
            $table->unsignedInteger('lease_months')->nullable()->after('lease_start');
            $table->string('agreement_path')->nullable()->after('agent_note');
            $table->timestamp('agreed_at')->nullable()->after('agreement_path');
        });
    }

    public function down(): void
    {
        $columns = Schema::getColumnListing('orders');
        $droppable = array_intersect(['type', 'lease_start', 'lease_months', 'agreement_path', 'agreed_at'], $columns);

        if ($droppable !== []) {
            Schema::table('orders', function (Blueprint $table) use ($droppable) {
                $table->dropColumn($droppable);
            });
        }
    }
};
