<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UiGatingTest extends TestCase
{
    use RefreshDatabase;

    private function users(): array
    {
        $this->seed();
        // Reset Spatie's permission cache so a prior test's cached registry
        // (stale model IDs after RefreshDatabase) can't leak grants across tests.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            User::where('email', 'admin@shelvi.test')->first(),
            User::where('email', 'accountant@shelvi.test')->first(),
        ];
    }

    public function test_create_buttons_respect_permissions(): void
    {
        [$admin, $accountant] = $this->users();

        // Admin can add banks; accountant cannot (banks read-only).
        $this->actingAs($admin)->get('/banks')->assertSee('Add account');
        $this->actingAs($accountant)->get('/banks')->assertDontSee('Add account');

        // Both can create parties.
        $this->actingAs($accountant)->get('/parties')->assertSee('New party');
    }

    /** Concatenated raw HTML of the parties table's action column for a user. */
    private function partiesActionHtml(User $user): string
    {
        $data = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->get('/parties?draw=1&start=0&length=10')->json('data');

        return collect($data)->pluck('action')->implode(' ');
    }

    public function test_admin_action_column_has_delete(): void
    {
        [$admin] = $this->users();
        $html = $this->partiesActionHtml($admin);
        $this->assertStringContainsString('/edit', $html);
        $this->assertStringContainsString('value="DELETE"', $html);
    }

    public function test_accountant_action_column_has_no_delete(): void
    {
        [, $accountant] = $this->users();
        $html = $this->partiesActionHtml($accountant);
        // Edit remains (parties.update), but no destroy form (lacks parties.delete).
        $this->assertStringContainsString('/edit', $html);
        $this->assertStringNotContainsString('value="DELETE"', $html);
    }
}
