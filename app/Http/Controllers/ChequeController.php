<?php

namespace App\Http\Controllers;

use App\DataTables\ChequesDataTable;
use App\Http\Requests\ChequeRequest;
use App\Models\Cheque;
use App\Repositories\Contracts\BankRepository;
use App\Repositories\Contracts\ChequeRepository;
use App\Repositories\Contracts\PartyRepository;
use App\Support\Csv;
use App\Support\Inr;
use App\Support\PdfCell;
use App\Support\PdfExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChequeController extends Controller
{
    public function __construct(
        private readonly ChequeRepository $cheques,
        private readonly PartyRepository $parties,
        private readonly BankRepository $banks,
    ) {}

    public function index(ChequesDataTable $dataTable)
    {
        return $dataTable->render('pages.cheques', [
            'stats' => $this->cheques->stats(),
        ]);
    }

    public function create()
    {
        return view('pages.cheques-form', [
            'cheque' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(ChequeRequest $request): RedirectResponse
    {
        Cheque::create([...$request->toModel(), 'direction' => 'received']);

        return redirect()->route('cheques')->with('success', 'Cheque created.');
    }

    public function edit(Cheque $cheque)
    {
        $row = $this->cheques->find((string) $cheque->id) ?? abort(404);

        $options = $this->formOptions();
        // Keep a soft-deleted party/bank selectable (dropdowns list active only).
        $options['parties'] = $this->keepCurrent($options['parties'], $row['party'] ?? null);
        $options['banksList'] = $this->keepCurrent($options['banksList'], $row['bankAccount'] ?? null, $row['bank'] ?? null);

        return view('pages.cheques-form', ['cheque' => $row, ...$options]);
    }

    /**
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

    public function update(ChequeRequest $request, Cheque $cheque): RedirectResponse
    {
        $cheque->update($request->toModel());

        return redirect()->route('cheques')->with('success', 'Cheque updated.');
    }

    public function destroy(Cheque $cheque): RedirectResponse
    {
        $cheque->delete();

        return redirect()->route('cheques')->with('success', 'Cheque deleted.');
    }

    /** Clear or bounce a cheque (dedicated permission). */
    public function verify(Request $request, Cheque $cheque): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['Cleared', 'Bounced'])],
        ]);

        $cheque->update([
            'status' => $data['status'],
            'deposit_date' => $cheque->deposit_date ?? now()->toDateString(),
        ]);

        return redirect()->route('cheques')->with('success', "Cheque marked {$data['status']}.");
    }

    /** All cheques, as CSV. */
    public function export(): StreamedResponse
    {
        $rows = $this->cheques->all()->map(fn ($r) => [
            $r['no'], $r['party'], $r['bank'], $r['issue'], $r['deposit'], $r['due'], $r['status'], $r['amount'],
        ]);

        return Csv::download(
            'cheques-'.now()->format('Y-m-d').'.csv',
            ['Cheque No', 'Party', 'Bank', 'Issue Date', 'Deposit Date', 'Due Date', 'Status', 'Amount'],
            $rows,
        );
    }

    private const STATUS_TONES = [
        'Cleared' => 'success',
        'Pending' => 'warning',
        'Bounced' => 'danger',
    ];

    /** All cheques, as a colour-coded PDF. */
    public function exportPdf()
    {
        $rows = $this->cheques->all()->map(fn ($r) => [
            PdfCell::plain($r['no']),
            PdfCell::plain($r['party']),
            PdfCell::plain($r['bank']),
            PdfCell::plain($r['issue']),
            $r['deposit'] ? PdfCell::plain($r['deposit']) : PdfCell::muted('—'),
            PdfCell::plain($r['due']),
            PdfCell::pill($r['status'], self::STATUS_TONES[$r['status']] ?? 'neutral'),
            PdfCell::plain(Inr::format($r['amount'])),
        ]);

        return PdfExport::download(
            'cheques-'.now()->format('Y-m-d').'.pdf',
            'Cheque Management',
            [
                ['label' => 'Cheque No'], ['label' => 'Party'], ['label' => 'Bank'],
                ['label' => 'Issue Date'], ['label' => 'Deposit Date'], ['label' => 'Due Date'],
                ['label' => 'Status'], ['label' => 'Amount', 'align' => 'right'],
            ],
            $rows,
            'Track issued and received cheques through clearing',
        );
    }

    /** Dropdowns + status list for the create + edit form. */
    private function formOptions(): array
    {
        return [
            'parties' => $this->parties->options(),
            'banksList' => $this->banks->options(),
            'statuses' => config('options.cheque_statuses'),
        ];
    }
}
