<?php

namespace App\Http\Controllers;

use App\DataTables\PaidDataTable;
use App\DataTables\ReceivedDataTable;
use App\Repositories\Contracts\BankRepository;
use App\Repositories\Contracts\PartyRepository;

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

    /** Party + bank dropdowns shared by both entry forms. */
    private function formOptions(): array
    {
        return [
            'parties' => $this->parties->options(),
            'banksList' => $this->banks->options(),
        ];
    }
}
