<?php

namespace App\Http\Controllers;

use App\DataTables\LedgerDataTable;
use App\Repositories\Contracts\LedgerRepository;
use App\Repositories\Contracts\PartyRepository;
use App\Support\Csv;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /** The currently selected party's ledger statement, as CSV. */
    public function export(Request $request): StreamedResponse
    {
        $party = $request->query('party');
        $ledgerParty = $party ?: $this->ledger->defaultParty();

        $rows = $this->ledger->rows($party)->map(fn ($r) => [
            $r['date'], $r['particulars'], $r['customer'], $r['vch'], $r['remark'],
            $r['debit'], $r['credit'], $r['balance'], $r['balType'],
        ]);

        return Csv::download(
            'ledger-'.str($ledgerParty)->slug().'-'.now()->format('Y-m-d').'.csv',
            ['Date', 'Particulars', 'Customer Name', 'Voucher', 'Remark', 'Debit', 'Credit', 'Balance', 'Type'],
            $rows,
        );
    }
}
