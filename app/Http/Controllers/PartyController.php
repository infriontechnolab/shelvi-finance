<?php

namespace App\Http\Controllers;

use App\DataTables\PartiesDataTable;
use App\Http\Requests\PartyRequest;
use App\Models\Party;
use App\Repositories\Contracts\PartyRepository;
use App\Support\Csv;
use App\Support\Inr;
use App\Support\PdfCell;
use App\Support\PdfExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartyController extends Controller
{
    public function __construct(private readonly PartyRepository $parties) {}

    public function index(PartiesDataTable $dataTable)
    {
        return $dataTable->render('pages.parties');
    }

    public function create()
    {
        return view('pages.parties-form', [
            'party' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(PartyRequest $request): RedirectResponse
    {
        Party::create($request->toModel());

        return redirect()->route('parties')->with('success', 'Party created.');
    }

    public function edit(Party $party)
    {
        return view('pages.parties-form', [
            'party' => $this->parties->find((string) $party->id) ?? abort(404),
            ...$this->formOptions(),
        ]);
    }

    public function update(PartyRequest $request, Party $party): RedirectResponse
    {
        $party->update($request->toModel());

        return redirect()->route('parties')->with('success', 'Party updated.');
    }

    public function destroy(Party $party): RedirectResponse
    {
        // Soft delete: the row (and its cheques/transactions/ledger history) stays in
        // the database, just hidden from the app. Recoverable, and FK-safe.
        $party->delete();

        return redirect()->route('parties')->with('success', 'Party deleted.');
    }

    /** All parties (respecting the list's category filter, if any), as CSV. */
    public function export(Request $request): StreamedResponse
    {
        $rows = $this->parties->all();
        $category = $request->query('category');
        if ($category) {
            $rows = $rows->where('category', $category)->values();
        }

        $rows = $rows->map(fn ($r) => [
            $r['name'], $r['category'], $r['phone'], $r['opening'],
            $r['current'], $r['balType'], $r['limit'], $r['status'],
        ]);

        return Csv::download(
            'parties-'.now()->format('Y-m-d').'.csv',
            ['Party', 'Category', 'Phone', 'Opening', 'Current Balance', 'Type', 'Credit Limit', 'Status'],
            $rows,
        );
    }

    private const CATEGORY_TONES = [
        'Customer' => 'info',
        'Vendor' => 'accent',
        'Finance Co' => 'indigo',
        'Agency' => 'warning',
    ];

    private const STATUS_TONES = [
        'Active' => 'success',
        'Inactive' => 'neutral',
    ];

    /** All parties (respecting the list's category filter, if any), as a colour-coded PDF. */
    public function exportPdf(Request $request)
    {
        $rows = $this->parties->all();
        $category = $request->query('category');
        if ($category) {
            $rows = $rows->where('category', $category)->values();
        }

        $rows = $rows->map(fn ($r) => [
            PdfCell::plain($r['name']),
            PdfCell::pill($r['category'], self::CATEGORY_TONES[$r['category']] ?? 'neutral'),
            $r['phone'] ? PdfCell::plain($r['phone']) : PdfCell::muted('—'),
            PdfCell::plain(Inr::format($r['opening'])),
            PdfCell::amount(Inr::format($r['current']), $r['balType'] === 'CR' ? 'negative' : 'positive').' '.PdfCell::drCr($r['balType']),
            PdfCell::plain(Inr::format($r['limit'])),
            PdfCell::pill($r['status'], self::STATUS_TONES[$r['status']] ?? 'neutral'),
        ]);

        return PdfExport::download(
            'parties-'.now()->format('Y-m-d').'.pdf',
            'Party Management',
            [
                ['label' => 'Party'], ['label' => 'Category'], ['label' => 'Phone'],
                ['label' => 'Opening', 'align' => 'right'], ['label' => 'Current Balance', 'align' => 'right'],
                ['label' => 'Credit Limit', 'align' => 'right'], ['label' => 'Status'],
            ],
            $rows,
            'Customers, vendors, finance companies and agencies',
        );
    }

    /** Static select lists for the create + edit form. */
    private function formOptions(): array
    {
        return [
            'categories' => config('options.party_categories'),
            'balTypes' => config('options.balance_types'),
            'statuses' => config('options.party_statuses'),
        ];
    }
}
