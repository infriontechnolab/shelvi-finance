<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payee's bank account details for a money-paid entry (who the payment
     * actually went to) — separate from `bank_id`, which is the source
     * account the payment was made FROM.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payee_holder')->nullable()->after('customer_name');
            $table->string('payee_account_no')->nullable()->after('payee_holder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['payee_holder', 'payee_account_no']);
        });
    }
};
