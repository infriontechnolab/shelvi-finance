<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Cheque;
use App\Models\Party;
use App\Models\User;
use App\Repositories\Contracts\ChequeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WritesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@shelvi.test')->first();
    }

    public function test_party_crud(): void
    {
        $admin = $this->admin();

        // create (rupees → paise, CR → negative opening)
        $this->actingAs($admin)->post('/parties', [
            'name' => 'Acme Co', 'category' => 'Vendor', 'phone' => '9990001112',
            'opening' => 1000, 'balType' => 'CR', 'limit' => 5000, 'status' => 'Active',
        ])->assertRedirect('/parties');

        $p = Party::where('name', 'Acme Co')->first();
        $this->assertNotNull($p);
        $this->assertSame(-100000, $p->opening_balance);  // 1000 * 100, CR negative
        $this->assertSame(500000, $p->credit_limit);

        // update
        $this->actingAs($admin)->put('/parties/'.$p->id, [
            'name' => 'Acme Corp', 'category' => 'Vendor', 'phone' => '9990001112',
            'opening' => 2000, 'balType' => 'CR', 'limit' => 5000, 'status' => 'Inactive',
        ])->assertRedirect('/parties');
        $this->assertSame('Acme Corp', $p->fresh()->name);
        $this->assertSame(-200000, $p->fresh()->opening_balance);

        // delete
        $this->actingAs($admin)->delete('/parties/'.$p->id)->assertRedirect('/parties');
        $this->assertNull(Party::find($p->id));
    }

    public function test_party_delete_is_soft_and_fk_safe(): void
    {
        $admin = $this->admin();
        $party = Party::has('cheques')->first();  // a hard delete would 500 on the FK
        $this->assertNotNull($party);

        $this->actingAs($admin)->delete('/parties/'.$party->id)->assertRedirect('/parties');
        $this->assertNull(Party::find($party->id));                   // hidden from the app
        $this->assertNotNull(Party::withTrashed()->find($party->id)); // row kept
        $this->assertNotNull($party->cheques()->first());            // history intact
    }

    public function test_bank_delete_is_soft_and_fk_safe(): void
    {
        $admin = $this->admin();
        $bank = Bank::has('cheques')->orHas('transactions')->first();
        $this->assertNotNull($bank);

        $this->actingAs($admin)->delete('/banks/'.$bank->id)->assertRedirect('/banks');
        $this->assertNull(Bank::find($bank->id));
        $this->assertNotNull(Bank::withTrashed()->find($bank->id));
    }

    public function test_soft_deleted_party_name_still_shows_on_cheques(): void
    {
        $this->admin();
        $cheque = Cheque::has('party')->with('party')->first();
        $party = $cheque->party;
        $party->delete();

        // Cheque list resolves the parent withTrashed, so the name doesn't vanish.
        $rows = app(ChequeRepository::class)->all();
        $this->assertTrue($rows->contains(fn ($r) => $r['party'] === $party->name));
    }

    public function test_party_validation_fails(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/parties', ['category' => 'Nope'])
            ->assertSessionHasErrors(['name', 'category', 'balType', 'status']);
    }

    public function test_bank_store(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/banks', [
            'name' => 'Test Bank', 'account' => '123456789012', 'type' => 'Current',
            'holder' => 'Shelvi', 'balance' => 1000,
        ])->assertRedirect('/banks');

        $b = Bank::where('name', 'Test Bank')->first();
        $this->assertSame(100000, $b->opening_balance);
        $this->assertSame('TE', $b->initials);
    }

    public function test_cheque_store_and_verify(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cheques', [
            'no' => '009999', 'amount' => 5000, 'party' => 'Mehta Traders', 'bank' => 'HDFC Bank',
            'issue' => '2025-06-20', 'due' => '2025-06-25', 'status' => 'Pending',
        ])->assertRedirect('/cheques');

        $c = Cheque::where('cheque_no', '009999')->first();
        $this->assertNotNull($c);
        $this->assertSame(500000, $c->amount);
        $this->assertNotNull($c->party_id);
        $this->assertSame('received', $c->direction);

        // verify → Cleared
        $this->actingAs($admin)->post('/cheques/'.$c->id.'/verify', ['status' => 'Cleared'])
            ->assertRedirect('/cheques');
        $this->assertSame('Cleared', $c->fresh()->status);
        $this->assertNotNull($c->fresh()->deposit_date);
    }
}
