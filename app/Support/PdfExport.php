<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;

/** Streams a colour-coded PDF download via the shared pdf.generic layout. */
class PdfExport
{
    /**
     * @param  array<int, array{label: string, align?: string}>  $columns
     * @param  iterable<array<int, string>>  $rows  pre-built HTML cells (see App\Support\PdfCell)
     */
    public static function download(string $filename, string $title, array $columns, iterable $rows, ?string $subtitle = null)
    {
        return Pdf::loadView('pdf.generic', [
            'title' => $title,
            'subtitle' => $subtitle,
            'columns' => $columns,
            'rows' => $rows,
        ])->setPaper('a4', 'landscape')->download($filename);
    }
}
