<?php

namespace App\DataTables;

use App\Support\Inr;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

/**
 * Shared base for all DataTables in the app.
 *
 * Holds global parameters (server-side, paging, layout, language, shadcn page-size
 * relocation) and reusable cell renderers (avatar, status pill, mono/bold/muted)
 * so concrete tables only declare their query + columns.
 */
abstract class BaseDataTable extends DataTable
{
    /** Initial page size. Override per table if needed. */
    protected int $defaultPageLength = 10;

    /**
     * Common DataTables parameters applied to every table.
     */
    protected function commonParameters(): array
    {
        return [
            'processing' => true,
            'serverSide' => true,
            'autoWidth' => false,
            'pageLength' => $this->defaultPageLength,
            'pagingType' => 'full_numbers',
            'layout' => [
                'topStart' => 'pageLength', // .dt-length slot (top-left) — combobox dropped in here
                'topEnd' => 'search',
                'bottomStart' => 'info',
                'bottomEnd' => 'paging',
            ],
            'language' => [
                'search' => 'Search:',
                'lengthMenu' => 'Show _MENU_ entries',
                'info' => 'Showing _START_ to _END_ of _TOTAL_ entries',
                'infoEmpty' => 'Showing 0 entries',
                'infoFiltered' => '(filtered from _MAX_)',
                'paginate' => ['previous' => 'Previous', 'next' => 'Next'],
                'processing' => 'Loading…',
            ],
            // Swap the native length <select> for the shadcn page-size combobox,
            // generically: each page provides <div id="{tableId}-pagelen" hidden>…combobox…</div>.
            // DataTables only auto-wires its OWN length select; our combobox is custom,
            // so we relocate it into the length slot AND bind it to the page.len API here
            // (once, centrally — no per-page change handlers needed).
            'initComplete' => 'function() {
                var api = this.api();
                var box = document.getElementById(api.table().node().id + "-pagelen");
                var cell = api.table().container().querySelector(".dt-length");
                if (!cell || !box) { return; }
                cell.innerHTML = "";
                cell.appendChild(box);
                box.hidden = false;
                var input = box.querySelector("[data-combobox-input]");
                if (input) {
                    input.addEventListener("change", function (e) {
                        api.page.len(parseInt(e.target.value, 10) || 10).draw();
                    });
                }
            }',
        ];
    }

    /**
     * Start an HtmlBuilder pre-loaded with the common config.
     */
    protected function baseBuilder(string $tableId): HtmlBuilder
    {
        return $this->builder()
            ->setTableId($tableId)
            ->addTableClass('w-full text-sm')
            ->parameters($this->commonParameters());
    }

    // ---- Cell renderers — render Blade partials in resources/views/datatables/cells.
    //      Markup + Tailwind classes live in Blade (scanned by Tailwind), so no
    //      @source inline safelist is needed. Columns using these go in rawColumns().

    /** Render a cell partial to an HTML string. */
    protected function cell(string $partial, array $data = []): string
    {
        return view("datatables.cells.$partial", $data)->render();
    }

    protected function avatar(string $name): string
    {
        return $this->cell('avatar', ['name' => $name]);
    }

    /** Status pill. $tones maps a status value to a semantic tone key. */
    protected function statusPill(string $status, array $tones): string
    {
        return $this->cell('status-pill', ['label' => $status, 'tone' => $tones[$status] ?? 'default']);
    }

    protected function mono(string $text): string
    {
        return $this->cell('text', ['value' => $text, 'variant' => 'mono']);
    }

    protected function bold(string $text): string
    {
        return $this->cell('text', ['value' => $text, 'variant' => 'bold']);
    }

    protected function muted(string $text): string
    {
        return $this->cell('text', ['value' => $text, 'variant' => 'muted']);
    }

    /** Plain INR amount (tabular), no sign colouring. */
    protected function money(int|float $n): string
    {
        return $this->cell('amount', ['value' => Inr::format($n), 'tone' => 'plain', 'bold' => false]);
    }

    /** Signed INR amount: green credit / red debit (finance convention). */
    protected function signedMoney(int|float $n): string
    {
        $tone = $n < 0 ? 'negative' : ($n > 0 ? 'positive' : 'muted');

        return $this->amount($n, $tone);
    }

    /** INR amount with an explicit semantic tone (positive / negative / muted / plain). */
    protected function amount(int|float $n, string $tone): string
    {
        return $this->cell('amount', ['value' => Inr::format($n), 'tone' => $tone, 'bold' => true]);
    }

    /** DR/CR balance-type pill. */
    protected function drCr(string $type): string
    {
        return $this->cell('dr-cr', ['type' => $type]);
    }

    /**
     * Row action buttons. Design-only: Edit links to its edit page (or '#'),
     * Delete opens the confirm dialog (handled in app.js). $only picks which to show.
     */
    protected function actions(string $id, array $only = ['edit', 'delete'], ?string $editUrl = null): string
    {
        return $this->cell('actions', [
            'id' => $id,
            'editUrl' => $editUrl,
            'showEdit' => in_array('edit', $only, true),
            'showDelete' => in_array('delete', $only, true),
            'deleteMessage' => 'Delete "'.$id.'"? This action cannot be undone.',
        ]);
    }
}
