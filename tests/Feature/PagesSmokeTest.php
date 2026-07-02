<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Cheque;
use App\Models\Party;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@shelvi.test')->first();
    }

    public function test_all_pages_render_for_admin(): void
    {
        $admin = $this->admin();

        foreach (['/', '/banks', '/parties', '/money-received', '/money-paid', '/ledger', '/cheques', '/reports'] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }

    public function test_edit_pages_and_404(): void
    {
        $admin = $this->admin();
        $partyId = Party::query()->value('id');
        $this->actingAs($admin)->get('/parties/'.$partyId.'/edit')->assertOk();
        $this->actingAs($admin)->get('/parties/999999/edit')->assertNotFound();
        $bankId = Bank::query()->value('id');
        $this->actingAs($admin)->get('/banks/'.$bankId.'/edit')->assertOk();
        $chequeId = Cheque::query()->value('id');
        $this->actingAs($admin)->get('/cheques/'.$chequeId.'/edit')->assertOk();
    }

    public function test_datatable_ajax_returns_json(): void
    {
        $admin = $this->admin();

        $res = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->get('/parties?draw=1&start=0&length=10');

        $res->assertOk()->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
        $this->assertSame(10, $res->json('recordsTotal'));

        $recent = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->get('/dashboard/recent-txns?draw=1&start=0&length=10');
        $recent->assertOk()->assertJsonStructure(['data']);
    }
}
