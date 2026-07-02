<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One line in a party's accounting journal. debit/credit are paise.
 * Running balance and DR/CR are derived when rendering (order by entry_date).
 */
#[Fillable(['party_id', 'entry_date', 'particulars', 'vch', 'debit', 'credit', 'transaction_id'])]
class LedgerEntry extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'debit' => 'integer',
            'credit' => 'integer',
        ];
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /** Chronological order for running-balance computation. */
    public function scopeChronological(Builder $q): Builder
    {
        return $q->orderBy('entry_date')->orderBy('id');
    }
}
