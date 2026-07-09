<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ReportRepository;
use App\Support\Csv;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private const PERIODS = ['all', 'today', 'week', 'month', 'quarter', 'year'];

    private const FORMATS = ['csv', 'pdf'];

    public function __construct(private readonly ReportRepository $reports) {}

    public function index()
    {
        return view('pages.reports', [
            'reportTypes' => $this->reports->types(),
        ]);
    }

    public function show(string $report, Request $request)
    {
        $data = $this->reports->generate($report, $this->period($request)) ?? abort(404);

        return view('pages.report-show', [
            'slug' => $report,
            'period' => $this->period($request),
            'periods' => self::PERIODS,
            ...$data,
        ]);
    }

    public function export(string $report, string $format, Request $request)
    {
        abort_unless(in_array($format, self::FORMATS, true), 404);

        $period = $this->period($request);
        $data = $this->reports->generate($report, $period) ?? abort(404);
        $file = $report.'-'.$period.'.'.$format;

        return $format === 'csv'
            ? Csv::download($file, array_column($data['columns'], 'label'), $data['rows'])
            : $this->pdf($data, $file);
    }

    private function period(Request $request): string
    {
        return in_array($request->query('period'), self::PERIODS, true)
            ? $request->query('period')
            : 'all';
    }

    private function pdf(array $data, string $file)
    {
        return Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($file);
    }
}
