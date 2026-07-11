<?php

namespace App\Http\Controllers;

use App\DataTables\LedgerDataTable;
use App\Repositories\Contracts\LedgerRepository;
use App\Repositories\Contracts\PartyRepository;
use App\Support\Csv;
use App\Support\Inr;
use App\Support\PdfCell;
use App\Support\PdfExport;
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
            $r['date'], $r['particulars'], $r['customer'], $r['method'], $r['bank'], $r['vch'],
            $r['payeeHolder'], $r['payeeAccount'], $r['remark'], $r['debit'], $r['credit'], $r['balance'], $r['balType'],
        ]);

        return Csv::download(
            'ledger-'.str($ledgerParty)->slug().'-'.now()->format('Y-m-d').'.csv',
            ['Date', 'Particulars', 'Customer Name', 'Method', 'Bank', 'Voucher', 'Account Holder', 'Account No', 'Remark', 'Debit', 'Credit', 'Balance', 'Type'],
            $rows,
        );
    }

    /** The currently selected party's ledger statement, as a colour-coded PDF. */
    public function exportPdf(Request $request)
    {
        $party = $request->query('party');
        $ledgerParty = $party ?: $this->ledger->defaultParty();

        $rows = $this->ledger->rows($party)->map(fn ($r) => [
            PdfCell::plain($r['date']),
            PdfCell::plain($r['particulars']),
            $r['customer'] === '-' ? PdfCell::muted('—') : PdfCell::plain($r['customer']),
            $r['method'] === '-' ? PdfCell::muted('—') : PdfCell::plain($r['method']),
            $r['bank'] === '-' ? PdfCell::muted('—') : PdfCell::plain($r['bank']),
            $r['vch'] === '-' ? PdfCell::muted('—') : PdfCell::plain($r['vch']),
            $r['payeeHolder'] === '-' ? PdfCell::muted('—') : PdfCell::plain($r['payeeHolder']),
            $r['payeeAccount'] === '-' ? PdfCell::muted('—') : PdfCell::plain($r['payeeAccount']),
            $r['remark'] === '-' ? PdfCell::muted('—') : PdfCell::plain($r['remark']),
            $r['debit'] > 0 ? PdfCell::amount(Inr::format($r['debit']), 'negative') : PdfCell::muted('—'),
            $r['credit'] > 0 ? PdfCell::amount(Inr::format($r['credit']), 'positive') : PdfCell::muted('—'),
            PdfCell::amount(Inr::format($r['balance']), $r['balType'] === 'DR' ? 'negative' : 'positive'),
            PdfCell::drCr($r['balType'], 'entry'),
        ]);

        return PdfExport::download(
            'ledger-'.str($ledgerParty)->slug().'-'.now()->format('Y-m-d').'.pdf',
            $ledgerParty.' — Ledger',
            [
                ['label' => 'Date'], ['label' => 'Particulars'], ['label' => 'Customer Name'], ['label' => 'Method'],
                ['label' => 'Bank'], ['label' => 'Voucher'], ['label' => 'Account Holder'], ['label' => 'Account No'], ['label' => 'Remark'],
                ['label' => 'Debit', 'align' => 'right'], ['label' => 'Credit', 'align' => 'right'],
                ['label' => 'Balance', 'align' => 'right'], ['label' => 'Type', 'align' => 'right'],
            ],
            $rows,
            'Statement of account with running balance',
        );
    }
}
