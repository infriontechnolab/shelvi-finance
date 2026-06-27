<?php

namespace App\Http\Controllers;

use App\DataTables\LedgerDataTable;
use App\Repositories\Contracts\LedgerRepository;
use App\Repositories\Contracts\PartyRepository;

class LedgerController extends Controller
{
    public function __construct(
        private readonly LedgerRepository $ledger,
        private readonly PartyRepository $parties,
    ) {}

    public function index(LedgerDataTable $dataTable)
    {
        return $dataTable->render('pages.ledger', [
            'parties' => $this->parties->options(),
            'ledgerParty' => $this->ledger->defaultParty(),
            'summary' => $this->ledger->summary(),
        ]);
    }
}
