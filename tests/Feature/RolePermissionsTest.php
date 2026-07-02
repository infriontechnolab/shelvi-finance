<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Cheque;
use App\Models\Party;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function users(): array
    {
        $this->seed();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            User::where('email', 'admin@shelvi.test')->first(),
            User::where('email', 'accountant@shelvi.test')->first(),
        ];
    }

    public function test_accountant_can_view_but_not_administer(): void
    {
        [, $accountant] = $this->users();

        // allowed (view + operate)
        $this->actingAs($accountant)->get('/')->assertOk();
        $this->actingAs($accountant)->get('/banks')->assertOk();
        $this->actingAs($accountant)->get('/parties')->assertOk();
        $this->actingAs($accountant)->get('/parties/create')->assertOk();
        $this->actingAs($accountant)->get('/cheques')->assertOk();

        // blocked (administer)
        $this->actingAs($accountant)->get('/banks/create')->assertForbidden();      // no banks.create
        $bankId = Bank::query()->value('id');
        $this->actingAs($accountant)->delete('/banks/'.$bankId)->assertForbidden();  // no banks.delete
        $partyId = Party::query()->value('id');
        $this->actingAs($accountant)->delete('/parties/'.$partyId)->assertForbidden(); // no parties.delete
    }

    public function test_accountant_can_operate_transactions_and_cheques(): void
    {
        [, $accountant] = $this->users();

        // has parties.create
        $this->actingAs($accountant)->post('/parties', [
            'name' => 'Op Co', 'category' => 'Customer', 'phone' => '9000000000',
            'opening' => 0, 'balType' => 'DR', 'limit' => 0, 'status' => 'Active',
        ])->assertRedirect('/parties');

        // has cheques.verify
        $chequeId = Cheque::query()->value('id');
        $this->actingAs($accountant)->post('/cheques/'.$chequeId.'/verify', ['status' => 'Cleared'])
            ->assertRedirect('/cheques');
    }

    public function test_admin_has_full_access(): void
    {
        [$admin] = $this->users();
        foreach (['/banks/create', '/parties/create', '/cheques/create'] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }

    public function test_gate_reflects_permissions_for_blade_can(): void
    {
        [$admin, $accountant] = $this->users();

        $this->assertTrue($admin->can('banks.delete'));
        $this->assertTrue($accountant->can('banks.view'));
        $this->assertFalse($accountant->can('banks.delete'));
        $this->assertFalse($accountant->can('users.create'));
    }
}
