<?php

namespace App\Http\Controllers;

use App\DataTables\BankTxnsDataTable;
use App\Http\Requests\BankRequest;
use App\Models\Bank;
use App\Repositories\Contracts\BankRepository;
use Illuminate\Http\RedirectResponse;

class BankController extends Controller
{
    public function __construct(private readonly BankRepository $banks) {}

    public function index(BankTxnsDataTable $dataTable)
    {
        return $dataTable->render('pages.banks', [
            'banks' => $this->banks->all(),
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
