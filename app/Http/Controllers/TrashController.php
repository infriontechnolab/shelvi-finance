<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Transaction;
use App\Support\Trash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

/**
 * Recycle-bin actions — superadmin only (every route carries a `trash.*`
 * permission, a hidden group no visible role can hold). There's no trash page:
 * each module's list has a "Show deleted" toggle whose Restore / Delete-forever
 * row actions post here.
 */
class TrashController extends Controller
{
    public function restore(string $type, string $id): RedirectResponse
    {
        $model = $this->trashed($type, $id);

        // A restored bank would collide if an active bank now holds its number.
        if ($model instanceof Bank
            && Bank::where('account_number', $model->account_number)->exists()) {
            return back()->with('error', "Can't restore “{$model->name}” — account number {$model->account_number} is in use by an active bank.");
        }

        $model->restore();

        // A transaction's ledger line was trashed with it — bring it back too.
        if ($model instanceof Transaction) {
            $model->ledgerEntry()->withTrashed()->restore();
        }

        return back()->with('success', 'Record restored.');
    }

    public function forceDelete(string $type, string $id): RedirectResponse
    {
        $model = $this->trashed($type, $id);

        if ($model instanceof Transaction) {
            $model->ledgerEntry()->withTrashed()->forceDelete();
        }

        $model->forceDelete();

        return back()->with('success', 'Record permanently deleted.');
    }

    /** Resolve a whitelisted type + id to its trashed model (404 otherwise). */
    private function trashed(string $type, string $id): Model
    {
        return Trash::model($type)::onlyTrashed()->findOrFail($id);
    }
}
