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
}
