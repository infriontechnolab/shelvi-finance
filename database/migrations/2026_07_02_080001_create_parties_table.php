<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Parties — customers, vendors, finance companies, agencies.
     * current_balance is NOT stored: it is derived from opening_balance ± transactions.
     * Money is stored as paise (integer) to avoid float drift; see App\Support\Inr.
     */
    public function up(): void
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');            // Customer | Vendor | Finance Co | Agency (config/options)
            $table->string('phone', 15)->nullable();
            $table->bigInteger('opening_balance')->default(0);   // paise
            $table->string('balance_type', 2)->default('DR');    // DR | CR
            $table->unsignedBigInteger('credit_limit')->default(0); // paise
            $table->string('status')->default('Active');         // Active | Inactive
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
