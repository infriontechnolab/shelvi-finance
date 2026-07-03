<?php

namespace App\Http\Controllers;

use App\DataTables\ChequesDataTable;
use App\Http\Requests\ChequeRequest;
use App\Models\Cheque;
use App\Repositories\Contracts\BankRepository;
use App\Repositories\Contracts\ChequeRepository;
use App\Repositories\Contracts\PartyRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $options['banksList'] = $this->keepCurrent($options['banksList'], $row['bank'] ?? null);

        return view('pages.cheques-form', ['cheque' => $row, ...$options]);
    }

    /**
     * @param  array<string, string>  $options
     * @return array<string, string>
     */
    private function keepCurrent(array $options, ?string $name): array
    {
        if ($name !== null && $name !== '' && ! array_key_exists($name, $options)) {
            $options[$name] = $name.' (deleted)';
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
