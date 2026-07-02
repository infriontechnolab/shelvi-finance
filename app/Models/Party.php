<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A party (Customer / Vendor / Finance Co / Agency).
 * Money columns are stored in paise (integer). current_balance is DERIVED,
 * not stored — see currentBalance().
 */
#[Fillable(['name', 'category', 'phone', 'opening_balance', 'balance_type', 'credit_limit', 'status'])]
class Party extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'opening_balance' => 'integer',
            'credit_limit' => 'integer',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function cheques(): HasMany
    {
        return $this->hasMany(Cheque::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Derived current balance in paise from the accounting journal:
     * opening + Σdebit − Σcredit. Positive = receivable (DR), negative = payable (CR).
     */
    public function currentBalance(): int
    {
        return $this->opening_balance
             + (int) $this->ledgerEntries()->sum('debit')
             - (int) $this->ledgerEntries()->sum('credit');
    }

    /** DR when balance ≥ 0 (receivable), CR when negative (payable). */
    public function balanceType(): string
    {
        return $this->currentBalance() >= 0 ? 'DR' : 'CR';
    }
}
