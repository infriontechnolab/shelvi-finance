<?php

namespace App\Http\Controllers;

use App\DataTables\BankTxnsDataTable;
use App\Repositories\Contracts\BankRepository;

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

    public function edit(string $bank)
    {
        return view('pages.banks-form', [
            'bank' => $this->banks->find($bank) ?? abort(404),
            'types' => config('options.bank_types'),
        ]);
    }
}
