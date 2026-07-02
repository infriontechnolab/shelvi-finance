<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@shelvi.test')->first();
    }

    public function test_catalogue_links_to_generators(): void
    {
        $this->actingAs($this->admin())->get('/reports')
            ->assertOk()->assertSee(route('reports.show', 'outstanding'));
    }

    public function test_every_report_generates(): void
    {
        $admin = $this->admin();
        $slugs = ['daily-collection', 'daily-payment', 'monthly-summary', 'bank-wise',
            'party-wise', 'outstanding', 'credit', 'debit', 'ledger'];

        foreach ($slugs as $slug) {
            $this->actingAs($admin)->get('/reports/'.$slug)->assertOk();
        }
    }

    public function test_outstanding_lists_a_party_balance(): void
    {
        // Mehta Traders carries a non-zero opening balance in the seed.
        $this->actingAs($this->admin())->get('/reports/outstanding')
            ->assertOk()->assertSee('Mehta Traders')->assertSee('receivable');
    }

    public function test_period_filter_narrows_results(): void
    {
        $admin = $this->admin();
        // Seed transactions are dated in 2025; "today" should be empty, "all" not.
        $this->actingAs($admin)->get('/reports/daily-collection?period=all')
            ->assertOk()->assertDontSee('No entries');
        $this->actingAs($admin)->get('/reports/daily-collection?period=today')
            ->assertOk()->assertSee('No entries');
    }

    public function test_unknown_report_is_404(): void
    {
        $this->actingAs($this->admin())->get('/reports/nope')->assertNotFound();
    }

    public function test_accountant_can_view_reports(): void
    {
        $this->seed();
        $accountant = User::where('email', 'accountant@shelvi.test')->first();
        $this->actingAs($accountant)->get('/reports/bank-wise')->assertOk();
    }
}
