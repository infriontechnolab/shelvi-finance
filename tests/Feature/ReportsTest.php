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

    public function test_csv_export_streams_rows(): void
    {
        $res = $this->actingAs($this->admin())->get('/reports/outstanding/export/csv');
        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $this->assertStringContainsString('Party,Balance,Type', $res->streamedContent());
        $this->assertStringContainsString('Mehta Traders', $res->streamedContent());
    }

    public function test_pdf_export_downloads(): void
    {
        $res = $this->actingAs($this->admin())->get('/reports/monthly-summary/export/pdf?period=all');
        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('content-type'));
    }

    public function test_unknown_export_format_is_404(): void
    {
        $this->actingAs($this->admin())->get('/reports/outstanding/export/xml')->assertNotFound();
    }

    public function test_accountant_can_view_reports(): void
    {
        $this->seed();
        $accountant = User::where('email', 'accountant@shelvi.test')->first();
        $this->actingAs($accountant)->get('/reports/bank-wise')->assertOk();
    }
}
