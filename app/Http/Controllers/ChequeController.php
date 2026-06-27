<?php

namespace App\Http\Controllers;

use App\DataTables\ChequesDataTable;
use App\Repositories\Contracts\BankRepository;
use App\Repositories\Contracts\ChequeRepository;
use App\Repositories\Contracts\PartyRepository;

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

    public function edit(string $cheque)
    {
        return view('pages.cheques-form', [
            'cheque' => $this->cheques->find($cheque) ?? abort(404),
            ...$this->formOptions(),
        ]);
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
