<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unified money movements: Money Received + Money Paid in one table.
     * Bank statement lines, party ledger, and dashboard KPIs are all QUERIES
     * over this table — no separate stored tables. amount is always positive;
     * the sign/meaning comes from `direction`.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable();      // NEFT-7723 / CHQ-… / CASH-…
            $table->string('direction');                  // received | paid
            $table->foreignId('party_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete(); // null = bank charge / non-party line
            $table->foreignId('bank_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('method');                     // Online | UPI | Cheque | Cash
            $table->unsignedBigInteger('amount');         // paise, always positive
            $table->string('status')->default('Pending'); // Pending | Cleared | Bounced
            $table->date('txn_date');
            $table->string('description')->nullable();     // bank-statement narration
            $table->foreignId('cheque_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['party_id', 'txn_date']);
            $table->index(['bank_id', 'txn_date']);
            $table->index('direction');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
