<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cheques — own lifecycle (issue → deposit → due → clear/bounce), distinct
     * from a settled transaction. A cleared cheque may later link to a transaction.
     */
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->string('cheque_no');
            $table->string('direction')->default('received'); // received (in) | issued (out)
            $table->foreignId('party_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('bank_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->bigInteger('amount');                 // paise
            $table->date('issue_date');
            $table->date('deposit_date')->nullable();     // null until deposited
            $table->date('due_date');
            $table->string('status')->default('Pending'); // Pending | Cleared | Bounced
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
