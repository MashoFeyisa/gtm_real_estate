<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A remittance of collected VAT to the tax authority (SRS §170).
 *
 * Recording a remittance posts the balanced journal "debit vat_payable, credit
 * cash" with the authority's reference, so the VAT report can show what is still
 * owed (the vat_payable balance) after each remittance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_remittances', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2);
            $table->date('period_start');
            $table->date('period_end');
            $table->string('reference');
            $table->string('ledger_group')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_remittances');
    }
};
