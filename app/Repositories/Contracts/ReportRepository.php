<?php

namespace App\Repositories\Contracts;

interface ReportRepository
{
    /** @return array<int, array> */
    public function types(): array;
}
