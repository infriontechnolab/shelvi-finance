<?php

namespace App\Repositories\Mock;

use App\Data\Mock;
use App\Repositories\Contracts\ReportRepository;

class MockReportRepository implements ReportRepository
{
    public function types(): array
    {
        return Mock::reportTypes();
    }

    /** Reference impl generates nothing — see EloquentReportRepository. */
    public function generate(string $slug, string $period): ?array
    {
        return null;
    }
}
