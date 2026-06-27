<?php

namespace App\Http\Controllers;

use App\DataTables\PartiesDataTable;
use App\Repositories\Contracts\PartyRepository;

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

    public function edit(string $party)
    {
        return view('pages.parties-form', [
            'party' => $this->parties->find($party) ?? abort(404),
            ...$this->formOptions(),
        ]);
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
