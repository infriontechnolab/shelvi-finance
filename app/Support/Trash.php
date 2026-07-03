<?php

namespace App\Support;

use App\Models\Bank;
use App\Models\Cheque;
use App\Models\LedgerEntry;
use App\Models\Party;
use App\Models\Transaction;

/**
 * Whitelist of soft-deletable modules for the recycle-bin actions. The `type`
 * slug (the only value trusted from the URL) maps to its model so
 * TrashController can restore / force-delete without trusting arbitrary input.
 */
final class Trash
{
    /** slug → soft-deletable model. */
    public const TYPES = [
        'parties' => Party::class,
        'banks' => Bank::class,
        'cheques' => Cheque::class,
        'transactions' => Transaction::class,
        'ledger' => LedgerEntry::class,
    ];

    /** Model class for a type, or 404 on anything unrecognised. */
    public static function model(string $type): string
    {
        return self::TYPES[$type] ?? abort(404);
    }
}
