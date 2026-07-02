<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bank accounts. account_number stored in FULL; masking is a view concern.
     * Running/current balance derived from opening_balance ± transactions (not stored).
     */
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('initials', 4)->nullable();   // avatar; derivable from name if null
            $table->string('account_number');            // full, unmasked
            $table->string('holder');
            $table->string('type')->default('Current');  // Current | Savings
            $table->bigInteger('opening_balance')->default(0); // paise
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
