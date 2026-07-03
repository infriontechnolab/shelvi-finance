<?php

namespace App\Repositories\Eloquent;

use App\Models\Party;
use App\Repositories\Contracts\PartyRepository;
use Illuminate\Support\Collection;

/**
 * Database-backed parties. Emits the same array shape the Mock repo did
 * (amounts in whole rupees) so DataTables/views need no changes.
 */
class EloquentPartyRepository implements PartyRepository
{
    public function all(): Collection
    {
        return Party::query()->orderBy('name')->get()->map(fn (Party $p) => $this->toRow($p));
    }

    public function deleted(): Collection
    {
        return Party::onlyTrashed()->orderBy('name')->get()->map(fn (Party $p) => $this->toRow($p));
    }

    public function find(string $id): ?array
    {
        $party = Party::query()->find($id);

        return $party ? $this->toRow($party) : null;
    }

    public function options(): array
    {
        return Party::query()->orderBy('name')->pluck('name', 'name')->all();
    }

    private function toRow(Party $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'category' => $p->category,
            'phone' => $p->phone,
            'opening' => intdiv(abs($p->opening_balance), 100),
            'current' => intdiv(abs($p->currentBalance()), 100),  // magnitude; side shown via balType
            'balType' => $p->balanceType(),
            'limit' => intdiv($p->credit_limit, 100),
            'status' => $p->status,
        ];
    }
}
