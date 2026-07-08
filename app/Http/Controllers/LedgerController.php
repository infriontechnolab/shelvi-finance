<?php

namespace App\Http\Controllers;

use App\DataTables\LedgerDataTable;
use App\Repositories\Contracts\LedgerRepository;
use App\Repositories\Contracts\PartyRepository;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function __construct(
        private readonly LedgerRepository $ledger,
        private readonly PartyRepository $parties,
    ) {}

    public function index(Request $request, LedgerDataTable $dataTable)
    {
        $party = $request->query('party');

        return $dataTable->render('pages.ledger', [
            'parties' => $this->parties->options(),
            'ledgerParty' => $party ?: $this->ledger->defaultParty(),
            'summary' => $this->ledger->summary($party),
        ]);
    }
}
