<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Free-text customer name for a money-paid/received entry, independent of
     * the linked party record (e.g. the actual payer named on a cash slip).
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('party_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('customer_name');
        });
    }
};
