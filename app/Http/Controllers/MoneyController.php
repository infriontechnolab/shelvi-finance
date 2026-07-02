<?php

namespace App\Http\Controllers;

use App\DataTables\PaidDataTable;
use App\DataTables\ReceivedDataTable;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Repositories\Contracts\BankRepository;
use App\Repositories\Contracts\PartyRepository;
use Illuminate\Http\RedirectResponse;

class MoneyController extends Controller
{
    public function __construct(
        private readonly PartyRepository $parties,
        private readonly BankRepository $banks,
    ) {}

    public function received(ReceivedDataTable $dataTable)
    {
        return $dataTable->render('pages.money-received', $this->formOptions());
    }

    public function paid(PaidDataTable $dataTable)
    {
        return $dataTable->render('pages.money-paid', $this->formOptions());
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $transaction = Transaction::create($request->toModel());
        $noun = $transaction->direction === 'received' ? 'Receipt' : 'Payment';

        return redirect()->to($this->listRoute($transaction))->with('success', "{$noun} recorded.");
    }

    public function edit(Transaction $transaction)
    {
        return view('pages.transactions-form', [
            'transaction' => $transaction->load(['party', 'bank']),
            ...$this->formOptions(),
        ]);
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $transaction->update($request->toModel());
        $noun = $transaction->direction === 'received' ? 'Receipt' : 'Payment';

        return redirect()->to($this->listRoute($transaction))->with('success', "{$noun} updated.");
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $route = $this->listRoute($transaction);
        $transaction->delete();

        return redirect()->to($route)->with('success', 'Entry deleted.');
    }

    /** Back to the list the entry belongs to (received vs paid). */
    private function listRoute(Transaction $transaction): string
    {
        return $transaction->direction === 'received' ? route('money-received') : route('money-paid');
    }

    /** Party + bank dropdowns and enums shared by both entry forms. */
    private function formOptions(): array
    {
        return [
            'parties' => $this->parties->options(),
            'banksList' => $this->banks->options(),
            'methods' => config('options.payment_methods'),
            'statuses' => config('options.transaction_statuses'),
        ];
    }
}
