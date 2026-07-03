<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\LedgerEntry;
use App\Models\Party;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrashTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $email): User
    {
        $this->seed();

        return User::where('email', $email)->firstOrFail();
    }

    private function superadmin(): User
    {
        return $this->user('owner@shelvi.test');
    }

    /** Pull a list DataTable's ajax rows as JSON. */
    private function feed(User $as, string $url): string
    {
        return collect(
            $this->actingAs($as)
                ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
                ->get($url)->json('data')
        )->toJson();
    }

    // ---- The gated "Show deleted" toggle ---------------------------------

    public function test_superadmin_toggle_shows_deleted_rows_only(): void
    {
        $admin = $this->superadmin();
        $party = Party::create(['name' => 'Ghost Traders', 'category' => 'customer', 'phone' => '9000000000', 'opening_balance' => 0]);
        $party->delete();

        // Default (active) feed hides it; trashed feed shows it.
        $this->assertStringNotContainsString('Ghost Traders', $this->feed($admin, '/parties'));
        $this->assertStringContainsString('Ghost Traders', $this->feed($admin, '/parties?trashed=1'));
    }

    public function test_non_superadmin_cannot_use_the_trashed_toggle(): void
    {
        $admin = $this->user('admin@shelvi.test');
        $party = Party::create(['name' => 'Ghost Traders', 'category' => 'customer', 'phone' => '9000000000', 'opening_balance' => 0]);
        $party->delete();

        // Admin lacks trash.view — the toggle is ignored, feed stays active-only.
        $this->assertStringNotContainsString('Ghost Traders', $this->feed($admin, '/parties?trashed=1'));
    }

    // ---- Restore / force delete (action endpoints) -----------------------

    public function test_restore_brings_a_party_back(): void
    {
        $admin = $this->superadmin();
        $party = Party::create(['name' => 'Ghost Traders', 'category' => 'customer', 'phone' => '9000000000', 'opening_balance' => 0]);
        $party->delete();

        $this->actingAs($admin)->post("/trash/parties/{$party->id}/restore")->assertRedirect();
        $this->assertNull($party->fresh()->deleted_at);
    }

    public function test_admin_cannot_restore(): void
    {
        $party = Party::create(['name' => 'Ghost Traders', 'category' => 'customer', 'phone' => '9000000000', 'opening_balance' => 0]);
        $party->delete();

        $this->actingAs($this->user('admin@shelvi.test'))
            ->post("/trash/parties/{$party->id}/restore")->assertForbidden();
    }

    public function test_force_delete_removes_the_row_entirely(): void
    {
        $admin = $this->superadmin();
        $party = Party::create(['name' => 'Gone Forever', 'category' => 'customer', 'phone' => '9000000000', 'opening_balance' => 0]);
        $party->delete();

        $this->actingAs($admin)->delete("/trash/parties/{$party->id}")->assertRedirect();
        $this->assertDatabaseMissing('parties', ['id' => $party->id]);
    }

    public function test_bank_restore_is_blocked_when_number_is_in_use(): void
    {
        $admin = $this->superadmin();
        $old = Bank::create(['name' => 'Old A/C', 'account_number' => 'DUP-1', 'holder' => 'X', 'type' => 'Current', 'opening_balance' => 0]);
        $old->delete();
        Bank::create(['name' => 'New A/C', 'account_number' => 'DUP-1', 'holder' => 'Y', 'type' => 'Current', 'opening_balance' => 0]);

        $this->actingAs($admin)->post("/trash/banks/{$old->id}/restore")->assertRedirect();
        $this->assertNotNull($old->fresh()->deleted_at, 'conflicting bank must stay trashed');
    }

    public function test_restoring_a_transaction_also_restores_its_ledger_line(): void
    {
        $admin = $this->superadmin();
        $party = Party::create(['name' => 'Ledger Party', 'category' => 'customer', 'phone' => '9000000000', 'opening_balance' => 0]);
        $bank = Bank::create(['name' => 'Main', 'account_number' => 'AC-9', 'holder' => 'H', 'type' => 'Current', 'opening_balance' => 0]);
        $txn = Transaction::create([
            'direction' => 'received', 'party_id' => $party->id, 'bank_id' => $bank->id,
            'method' => 'Online', 'amount' => 500000, 'txn_date' => '2025-05-01', 'status' => 'Cleared',
        ]);
        $ledger = $txn->ledgerEntry()->create([
            'party_id' => $party->id, 'entry_date' => '2025-05-01', 'particulars' => 'Amount received',
            'vch' => 'REC-1', 'debit' => 0, 'credit' => 500000,
        ]);
        $txn->ledgerEntry()->delete();
        $txn->delete();

        $this->actingAs($admin)->post("/trash/transactions/{$txn->id}/restore")->assertRedirect();

        $this->assertNull($txn->fresh()->deleted_at);
        $this->assertNull(LedgerEntry::withTrashed()->find($ledger->id)->deleted_at);
    }

    public function test_unknown_type_is_404(): void
    {
        $this->actingAs($this->superadmin())->post('/trash/widgets/1/restore')->assertNotFound();
    }
}
