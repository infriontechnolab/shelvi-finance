<?php

namespace App\Repositories\Contracts;

interface ReportRepository
{
    /** The report catalogue (title, desc, icon, slug). @return array<int, array> */
    public function types(): array;

    /**
     * Build a report: resolves a slug + period into a table (columns + rows).
     * Returns null for an unknown slug.
     *
     * @return array{title:string, desc:string, icon:string, periodLabel:string, columns:array, rows:array}|null
     */
    public function generate(string $slug, string $period): ?array;
}
