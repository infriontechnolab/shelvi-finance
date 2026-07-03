<?php

namespace App\Models\Concerns;

/**
 * Resolve a display name to a primary key, tolerating soft-deleted rows.
 *
 * Dropdowns only list active rows, but an existing cheque/transaction may still
 * point at a soft-deleted party or bank. When such a record is re-saved the
 * name must still resolve to its id (an active row wins over a trashed one) so
 * the link isn't silently dropped.
 */
trait ResolvableByName
{
    public static function idForName(?string $name): ?int
    {
        if ($name === null || $name === '') {
            return null;
        }

        return static::withTrashed()
            ->where('name', $name)
            ->orderByRaw('deleted_at is null desc') // prefer the active row
            ->value('id');
    }
}
