<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A money movement (received or paid). Bank statement, party ledger, and
 * dashboard aggregates are all queries over this table. amount is paise, positive.
 */
#[Fillable(['reference', 'direction', 'party_id', 'customer_name', 'payee_holder', 'payee_account_no', 'bank_id', 'method', 'amount', 'status', 'txn_date', 'description', 'remark', 'cheque_id'])]
class Transaction extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'txn_date' => 'date',
        ];
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function cheque(): BelongsTo
    {
        return $this->belongsTo(Cheque::class);
    }

    /** The party-ledger line this money movement posts (kept in sync by MoneyController). */
    public function ledgerEntry(): HasOne
    {
        return $this->hasOne(LedgerEntry::class);
    }

    public function scopeReceived(Builder $q): Builder
    {
        return $q->where('direction', 'received');
    }

    public function scopePaid(Builder $q): Builder
    {
        return $q->where('direction', 'paid');
    }
}
