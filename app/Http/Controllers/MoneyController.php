<?php

namespace App\Http\Controllers;

use App\DataTables\PaidDataTable;
use App\DataTables\ReceivedDataTable;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Repositories\Contracts\BankRepository;
use App\Repositories\Contracts\MoneyRepository;
use App\Repositories\Contracts\PartyRepository;
use App\Support\Csv;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MoneyController extends Controller
{
    public function __construct(
        private readonly PartyRepository $parties,
        private readonly BankRepository $banks,
        private readonly MoneyRepository $money,
    ) {}

    public function received(ReceivedDataTable $dataTable)
    {
        return $dataTable->render('pages.money-received', $this->formOptions());
    }

    public function paid(PaidDataTable $dataTable)
    {
        return $dataTable->render('pages.money-paid', $this->formOptions());
    }

    /** All recorded receipts, as CSV. */
    public function exportReceived(): StreamedResponse
    {
        return $this->exportMoney($this->money->received(), 'money-received', received: true);
    }

    /** All recorded payments (including payee bank details), as CSV. */
    public function exportPaid(): StreamedResponse
    {
        return $this->exportMoney($this->money->paid(), 'money-paid', received: false);
    }

    private function exportMoney(Collection $entries, string $file, bool $received): StreamedResponse
    {
        $headers = ['Voucher', 'Date', 'Party', 'Customer Name', 'Method', 'Bank', 'Vehicle No'];
        if (! $received) {
            $headers = [...$headers, 'Account Holder', 'Account No'];
        }
        $headers = [...$headers, 'Remark', 'Status', 'Amount'];

        $rows = $entries->map(function ($r) use ($received) {
            $row = [$r['id'], $r['date'], $r['party'], $r['customer'], $r['method'], $r['bank'], $r['ref']];
            if (! $received) {
                $row = [...$row, $r['payeeHolder'], $r['payeeAccount']];
            }

            return [...$row, $r['remark'], $r['status'], $r['amount']];
        });

        return Csv::download($file.'-'.now()->format('Y-m-d').'.csv', $headers, $rows);
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $transaction = Transaction::create($request->toModel());
        $this->postLedger($transaction);
        $noun = $transaction->direction === 'received' ? 'Receipt' : 'Payment';

        return redirect()->to($this->listRoute($transaction))->with('success', "{$noun} recorded.");
    }

    public function edit(Transaction $transaction)
    {
        // Load the party/bank even if soft-deleted so the form can name them.
        $transaction->load([
            'party' => fn ($q) => $q->withTrashed(),
            'bank' => fn ($q) => $q->withTrashed(),
        ]);

        $options = $this->formOptions();
        $options['parties'] = $this->keepCurrent($options['parties'], $transaction->party?->name);
        $options['banksList'] = $this->keepCurrent($options['banksList'], $transaction->bank?->account_number, $transaction->bank?->label());

        return view('pages.transactions-form', ['transaction' => $transaction, ...$options]);
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $transaction->update($request->toModel());
        $this->postLedger($transaction);
        $noun = $transaction->direction === 'received' ? 'Receipt' : 'Payment';

        return redirect()->to($this->listRoute($transaction))->with('success', "{$noun} updated.");
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $route = $this->listRoute($transaction);
        // Soft-delete the linked ledger line too so the party balance reverts.
        $transaction->ledgerEntry()->delete();
        $transaction->delete();

        return redirect()->to($route)->with('success', 'Entry deleted.');
    }

    /**
     * Keep the party-ledger line in sync with a money movement so balances stay
     * correct. Received reduces a receivable (credit); paid reduces a payable (debit).
     */
    private function postLedger(Transaction $transaction): void
    {
        if (! $transaction->party_id) {
            return; // non-party line (e.g. bank charge) — nothing to post
        }

        $received = $transaction->direction === 'received';

        $transaction->ledgerEntry()->updateOrCreate([], [
            'party_id' => $transaction->party_id,
            'entry_date' => $transaction->txn_date,
            'particulars' => $received ? 'Amount received' : 'Amount paid',
            'vch' => $transaction->reference ?: (($received ? 'REC-' : 'PAY-').$transaction->id),
            'debit' => $received ? 0 : $transaction->amount,
            'credit' => $received ? $transaction->amount : 0,
        ]);
    }

    /** Back to the list the entry belongs to (received vs paid). */
    private function listRoute(Transaction $transaction): string
    {
        return $transaction->direction === 'received' ? route('money-received') : route('money-paid');
    }

    /**
     * Keep a soft-deleted party/bank selectable on the edit form: dropdowns list
     * only active rows, so append the record's current one if it's missing.
     *
     * @param  array<string, string>  $options
     * @return array<string, string>
     */
    private function keepCurrent(array $options, ?string $key, ?string $label = null): array
    {
        if ($key !== null && $key !== '' && ! array_key_exists($key, $options)) {
            $options[$key] = ($label ?? $key).' (deleted)';
        }

        return $options;
    }

    /** Party + bank dropdowns and enums shared by both entry forms. */
    private function formOptions(): array
    {
        return [
            'parties' => $this->parties->options(),
            'banksList' => $this->banks->options(),
            'methods' => config('options.payment_methods'),
        ];
    }
}
