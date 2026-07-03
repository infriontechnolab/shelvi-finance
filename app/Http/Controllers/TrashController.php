<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Cheque;
use App\Models\LedgerEntry;
use App\Models\Party;
use App\Models\Transaction;
use App\Support\Dates;
use App\Support\Inr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

/**
 * Recycle bin for soft-deleted records — superadmin only (every route carries
 * a `trash.*` permission, a hidden group no visible role can hold). Lists the
 * trashed rows of each core entity and offers Restore / Delete-forever.
 */
class TrashController extends Controller
{
    /** slug → soft-deletable model. The slug is the only value accepted from the URL. */
    private const TYPES = [
        'parties' => Party::class,
        'banks' => Bank::class,
        'cheques' => Cheque::class,
        'transactions' => Transaction::class,
        'ledger' => LedgerEntry::class,
    ];

    private const META = [
        'parties' => ['label' => 'Parties', 'icon' => 'users'],
        'banks' => ['label' => 'Bank Accounts', 'icon' => 'landmark'],
        'cheques' => ['label' => 'Cheques', 'icon' => 'wallet'],
        'transactions' => ['label' => 'Transactions', 'icon' => 'arrow-left-right'],
        'ledger' => ['label' => 'Ledger Entries', 'icon' => 'book-open'],
    ];

    public function index()
    {
        $sections = collect(self::TYPES)
            ->map(fn (string $model, string $type) => [
                'type' => $type,
                'label' => self::META[$type]['label'],
                'icon' => self::META[$type]['icon'],
                'rows' => $this->rows($type),
            ])
            ->values()->all();

        return view('pages.trash', ['sections' => $sections]);
    }

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

        // Drop the linked ledger line first so nothing dangles.
        if ($model instanceof Transaction) {
            $model->ledgerEntry()->withTrashed()->forceDelete();
        }

        $model->forceDelete();

        return back()->with('success', 'Record permanently deleted.');
    }

    /** Resolve a whitelisted type + id to its trashed model (404 otherwise). */
    private function trashed(string $type, string $id): Model
    {
        $model = self::TYPES[$type] ?? abort(404);

        return $model::onlyTrashed()->findOrFail($id);
    }

    /** @return array<int, array{id:int, primary:string, secondary:string, when:string}> */
    private function rows(string $type): array
    {
        $query = (self::TYPES[$type])::onlyTrashed()->latest('deleted_at');

        if (in_array($type, ['cheques', 'transactions', 'ledger'], true)) {
            $query->with(['party' => fn ($q) => $q->withTrashed()]);
        }
        // A transaction's own ledger line is managed via the transaction — hide it here.
        if ($type === 'ledger') {
            $query->whereNull('transaction_id');
        }

        return $query->get()->map(fn (Model $m) => $this->present($type, $m))->all();
    }

    /** @return array{id:int, primary:string, secondary:string, when:string} */
    private function present(string $type, Model $m): array
    {
        $when = Dates::human($m->deleted_at->format('Y-m-d'));

        [$primary, $secondary] = match ($type) {
            'parties' => [$m->name, ucfirst((string) $m->category)],
            'banks' => [$m->name, $m->account_number],
            'cheques' => ['Cheque '.$m->cheque_no, ($m->party?->name ?? '—').' · '.Inr::format(intdiv($m->amount, 100))],
            'transactions' => [ucfirst($m->direction).' · '.Inr::format(intdiv($m->amount, 100)), $m->party?->name ?? '—'],
            'ledger' => [$m->particulars, ($m->party?->name ?? '—').' · '.Inr::format(intdiv(max($m->debit, $m->credit), 100))],
        };

        return ['id' => $m->id, 'primary' => $primary, 'secondary' => $secondary, 'when' => $when];
    }
}
