<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A cheque with its own lifecycle: issue → deposit → due → clear/bounce.
 * amount is paise. Links to transactions once settled.
 */
#[Fillable(['cheque_no', 'direction', 'party_id', 'bank_id', 'amount', 'issue_date', 'deposit_date', 'due_date', 'status'])]
class Cheque extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'issue_date' => 'date',
            'deposit_date' => 'date',
            'due_date' => 'date',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeStatus(Builder $q, string $status): Builder
    {
        return $q->where('status', $status);
    }
}
