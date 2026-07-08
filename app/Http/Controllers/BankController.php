<?php

namespace App\Http\Controllers;

use App\DataTables\BankTxnsDataTable;
use App\Http\Requests\BankRequest;
use App\Models\Bank;
use App\Repositories\Contracts\BankRepository;
use App\Support\Access;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function __construct(private readonly BankRepository $banks) {}

    public function index(BankTxnsDataTable $dataTable, Request $request)
    {
        // Banks render as cards, so the "show deleted" toggle is a superadmin-only
        // page mode (?trashed=1) that swaps the cards to soft-deleted accounts.
        $trashed = $request->boolean('trashed') && Access::isSuperAdmin($request->user());

        return $dataTable->render('pages.banks', [
            'banks' => $trashed ? $this->banks->deleted() : $this->banks->all(),
            'trashed' => $trashed,
        ]);
    }

    public function create()
    {
        return view('pages.banks-form', [
            'bank' => null,
            'types' => config('options.bank_types'),
        ]);
    }

    public function store(BankRequest $request): RedirectResponse
    {
        Bank::create($request->toModel());

        return redirect()->route('banks')->with('success', 'Bank account added.');
    }

    public function edit(Bank $bank)
    {
        return view('pages.banks-form', [
            'bank' => $this->banks->find((string) $bank->id) ?? abort(404),
            'types' => config('options.bank_types'),
        ]);
    }

    public function update(BankRequest $request, Bank $bank): RedirectResponse
    {
        $bank->update($request->toModel());

        if ($deposit = $request->depositAmount()) {
            $bank->transactions()->create([
                'direction' => 'received',
                'method' => 'Cash',
                'amount' => $deposit,
                'status' => 'Cleared',
                'txn_date' => now(),
                'description' => 'Manual deposit',
            ]);
        }

        return redirect()->route('banks')->with('success', 'Bank account updated.');
    }

    public function destroy(Bank $bank): RedirectResponse
    {
        // Soft delete: row + linked cheques/transactions stay in the database, just
        // hidden from the app. Recoverable, and FK-safe.
        $bank->delete();

        return redirect()->route('banks')->with('success', 'Bank account deleted.');
    }
}
