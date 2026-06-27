<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ReportRepository;

class ReportController extends Controller
{
    public function __construct(private readonly ReportRepository $reports) {}

    public function index()
    {
        return view('pages.reports', [
            'reportTypes' => $this->reports->types(),
        ]);
    }
}
