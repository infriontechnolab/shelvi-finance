<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Party;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SetupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_admin_only_with_no_demo_data(): void
    {
        $this->artisan('app:setup')
            ->expectsQuestion('Administrator name', 'Administrator')
            ->expectsQuestion('Administrator email', 'admin@acme.com')
            ->expectsQuestion('Administrator password', 'super-secret-1')
            ->expectsConfirmation('Also create an accountant account?', 'no')
            ->expectsConfirmation('Create the internal owner account? (manages users/roles, hidden in UI — keep private)', 'no')
            ->assertExitCode(0);

        $admin = User::where('email', 'admin@acme.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue(Hash::check('super-secret-1', $admin->password));

        // Roles installed, no demo domain data.
        $this->assertSame(1, User::count());
        $this->assertSame(0, Party::count());
        $this->assertSame(0, Bank::count());
    }

    public function test_creates_accountant_and_internal_owner_when_confirmed(): void
    {
        $this->artisan('app:setup')
            ->expectsQuestion('Administrator name', 'Administrator')
            ->expectsQuestion('Administrator email', 'admin@acme.com')
            ->expectsQuestion('Administrator password', 'super-secret-1')
            ->expectsConfirmation('Also create an accountant account?', 'yes')
            ->expectsQuestion('Accountant name', 'Accountant')
            ->expectsQuestion('Accountant email', 'accounts@acme.com')
            ->expectsQuestion('Accountant password', 'super-secret-2')
            ->expectsConfirmation('Create the internal owner account? (manages users/roles, hidden in UI — keep private)', 'yes')
            ->expectsQuestion('Owner name', 'System Owner')
            ->expectsQuestion('Owner email', 'owner@internal.com')
            ->expectsQuestion('Owner password', 'super-secret-3')
            ->assertExitCode(0);

        $this->assertTrue(User::where('email', 'accounts@acme.com')->first()->hasRole('accountant'));
        $this->assertTrue(User::where('email', 'owner@internal.com')->first()->hasRole('superadmin'));
        $this->assertSame(3, User::count());
    }
}
