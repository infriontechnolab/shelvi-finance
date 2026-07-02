<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-party accounting journal (invoices + payment lines). This is the
     * source of a party's ledger and true receivable/payable balance.
     * transactions stays money-only; a payment line links back via transaction_id.
     * Running balance is DERIVED (order by date) — not stored.
     */
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('particulars');
            $table->string('vch')->nullable();       // voucher no: INV-2001 / REC-501
            $table->bigInteger('debit')->default(0);  // paise
            $table->bigInteger('credit')->default(0); // paise
            $table->foreignId('transaction_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['party_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
