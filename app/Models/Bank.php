<?php

namespace App\Models;

use App\Models\Concerns\ResolvableByName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A bank account. account_number stored in FULL; masking is a view concern.
 * Balance is DERIVED from opening_balance ± transactions — not stored.
 */
#[Fillable(['name', 'initials', 'account_number', 'holder', 'type', 'opening_balance'])]
class Bank extends Model
{
    use ResolvableByName, SoftDeletes;

    protected function casts(): array
    {
        return [
            'opening_balance' => 'integer',
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

    /** Derived running balance in paise: opening + received − paid. */
    public function currentBalance(): int
    {
        return $this->opening_balance
             + (int) $this->transactions()->where('direction', 'received')->sum('amount')
             - (int) $this->transactions()->where('direction', 'paid')->sum('amount');
    }

    /** Last-4 masked form for display: "XXXX XXXX 4821". */
    public function maskedAccount(): string
    {
        $last4 = substr(preg_replace('/\s+/', '', $this->account_number), -4);

        return 'XXXX XXXX '.$last4;
    }
}
