<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ReportRepository;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private const PERIODS = ['all', 'today', 'week', 'month', 'quarter', 'year'];

    public function __construct(private readonly ReportRepository $reports) {}

    public function index()
    {
        return view('pages.reports', [
            'reportTypes' => $this->reports->types(),
        ]);
    }

    public function show(string $report, Request $request)
    {
        $period = in_array($request->query('period'), self::PERIODS, true)
            ? $request->query('period')
            : 'all';

        $data = $this->reports->generate($report, $period) ?? abort(404);

        return view('pages.report-show', [
            'slug' => $report,
            'period' => $period,
            'periods' => self::PERIODS,
            ...$data,
        ]);
    }
}
