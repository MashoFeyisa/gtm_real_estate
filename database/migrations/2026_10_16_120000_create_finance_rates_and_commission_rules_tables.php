<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database tax engine (SRS §171) and the commission rule book (§99).
     *
     * A `finance_rate` is a dated fact: "from this day, this key is this rate".
     * Nothing is ever edited in place - a change is a new row with a later
     * `effective_from`, so an old payment can always be explained by the rule
     * that was live when it was written. `commission_rules` narrows the default
     * commission by company, property type or agent, again with an effective
     * date, and the most specific live rule wins.
     */
    public function up(): void
    {
        Schema::create('finance_rates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->decimal('rate', 8, 4);
            $table->date('effective_from')->index();
            $table->date('effective_to')->nullable();
            $table->string('location')->nullable();
            $table->string('service')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['key', 'effective_from']);
        });

        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            // `company_id` is deliberately not a foreign key: the platform has
            // no companies table yet. It is the grouping an operator would use
            // once one exists, and is matched by value today.
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('property_type')->nullable();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->decimal('rate', 8, 4);
            $table->date('effective_from')->index();
            $table->date('effective_to')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'property_type', 'company_id', 'effective_from'], 'commission_rules_scope_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('finance_rates');
    }
};
