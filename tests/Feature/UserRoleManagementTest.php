<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Access;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: User, 2: User} [superadmin, admin, accountant] */
    private function users(): array
    {
        $this->seed();

        return [
            User::where('email', 'owner@shelvi.test')->first(),
            User::where('email', 'admin@shelvi.test')->first(),
            User::where('email', 'accountant@shelvi.test')->first(),
        ];
    }

    public function test_only_superadmin_reaches_user_and_role_admin(): void
    {
        [$superadmin, $admin, $accountant] = $this->users();

        // Superadmin: full access to both surfaces.
        $this->actingAs($superadmin)->get('/users')->assertOk();
        $this->actingAs($superadmin)->get('/users/create')->assertOk();
        $this->actingAs($superadmin)->get('/roles')->assertOk();
        $this->actingAs($superadmin)->get('/roles/create')->assertOk();

        // Admin: everything else, but NOT user/role administration.
        $this->actingAs($admin)->get('/users')->assertForbidden();
        $this->actingAs($admin)->get('/roles')->assertForbidden();
        $this->actingAs($admin)->get('/parties')->assertOk();

        // Accountant: also blocked.
        $this->actingAs($accountant)->get('/users')->assertForbidden();
        $this->actingAs($accountant)->get('/roles')->assertForbidden();
    }

    public function test_admin_role_excludes_user_and_role_permissions(): void
    {
        $this->seed();
        $admin = Role::where('name', 'admin')->first();
        $names = $admin->permissions->pluck('name');

        $this->assertTrue($names->contains('parties.delete'));
        $this->assertFalse($names->contains(fn ($n) => str_starts_with($n, 'users.')));
        $this->assertFalse($names->contains(fn ($n) => str_starts_with($n, 'roles.')));
    }

    public function test_create_user_with_role(): void
    {
        [$superadmin] = $this->users();
        $this->actingAs($superadmin)->post('/users', [
            'name' => 'Neha Rao', 'email' => 'neha@shelvi.test', 'password' => 'password123',
            'role' => 'accountant', 'is_active' => '1',
        ])->assertRedirect('/users');

        $u = User::where('email', 'neha@shelvi.test')->first();
        $this->assertNotNull($u);
        $this->assertTrue($u->hasRole('accountant'));
        $this->assertTrue($u->is_active);
    }

    public function test_cannot_delete_self(): void
    {
        // The only user-manager is the (hidden) superadmin: deleting itself is
        // caught by the secrecy guard (404) before the self-delete rule — either
        // way the account survives.
        [$superadmin] = $this->users();
        $this->actingAs($superadmin)->delete('/users/'.$superadmin->id)->assertNotFound();
        $this->assertNotNull(User::find($superadmin->id));
    }

    public function test_cannot_demote_last_admin(): void
    {
        [$superadmin, $admin] = $this->users();
        $this->actingAs($superadmin)->put('/users/'.$admin->id, [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'accountant', 'is_active' => '1',
        ])->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_edit_role_permissions(): void
    {
        [$superadmin] = $this->users();
        $this->actingAs($superadmin)->put('/roles/'.Role::where('name', 'accountant')->value('id'), [
            'permissions' => ['dashboard.view', 'parties.view'],
        ])->assertRedirect('/roles');

        $acct = Role::where('name', 'accountant')->first();
        $this->assertEqualsCanonicalizing(['dashboard.view', 'parties.view'], $acct->permissions->pluck('name')->all());
    }

    // --- Secrecy: the superadmin must appear not to exist -------------------

    public function test_superadmin_role_is_hidden_from_listing(): void
    {
        [$superadmin] = $this->users();
        $roleId = Role::where('name', Access::SUPERADMIN)->value('id');
        // The listing shows the manageable roles but never links to the superadmin.
        $this->actingAs($superadmin)->get('/roles')
            ->assertSee(route('roles.edit', Role::where('name', 'accountant')->value('id')))
            ->assertDontSee(route('roles.edit', $roleId));
    }

    public function test_superadmin_role_cannot_be_edited(): void
    {
        [$superadmin] = $this->users();
        $roleId = Role::where('name', Access::SUPERADMIN)->value('id');
        $this->actingAs($superadmin)->get('/roles/'.$roleId.'/edit')->assertNotFound();
        $this->actingAs($superadmin)->put('/roles/'.$roleId, ['permissions' => []])->assertNotFound();
    }

    public function test_superadmin_user_is_hidden_and_unreachable(): void
    {
        [$superadmin] = $this->users();
        // Not in the users DataTable data feed.
        $rows = $this->actingAs($superadmin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->get('/users?draw=1&start=0&length=10')->json('data');
        $this->assertStringNotContainsString('owner@shelvi.test', collect($rows)->toJson());
        // Not reachable by direct URL.
        $this->actingAs($superadmin)->get('/users/'.$superadmin->id.'/edit')->assertNotFound();
        $this->actingAs($superadmin)->delete('/users/'.$superadmin->id)->assertNotFound();
    }

    public function test_cannot_assign_superadmin_role_to_a_user(): void
    {
        [$superadmin] = $this->users();
        $this->actingAs($superadmin)->post('/users', [
            'name' => 'Sneaky', 'email' => 'sneaky@shelvi.test', 'password' => 'password123',
            'role' => Access::SUPERADMIN, 'is_active' => '1',
        ])->assertSessionHasErrors('role');
        $this->assertNull(User::where('email', 'sneaky@shelvi.test')->first());
    }

    // --- Create-role feature ------------------------------------------------

    public function test_superadmin_creates_a_custom_role(): void
    {
        [$superadmin] = $this->users();
        $this->actingAs($superadmin)->post('/roles', [
            'name' => 'Auditor', 'permissions' => ['dashboard.view', 'reports.view', 'reports.export'],
        ])->assertRedirect('/roles');

        $role = Role::where('name', 'auditor')->first();
        $this->assertNotNull($role);
        $this->assertEqualsCanonicalizing(
            ['dashboard.view', 'reports.view', 'reports.export'],
            $role->permissions->pluck('name')->all(),
        );
    }

    public function test_cannot_create_role_named_superadmin(): void
    {
        [$superadmin] = $this->users();
        $this->actingAs($superadmin)->post('/roles', ['name' => 'SuperAdmin', 'permissions' => []])
            ->assertSessionHasErrors('name');
        $this->assertSame(1, Role::where('name', Access::SUPERADMIN)->count());
    }

    public function test_visible_role_cannot_be_granted_hidden_permissions(): void
    {
        [$superadmin] = $this->users();
        // Crafted request trying to smuggle users.create into the accountant role.
        $this->actingAs($superadmin)->put('/roles/'.Role::where('name', 'accountant')->value('id'), [
            'permissions' => ['dashboard.view', 'users.create'],
        ])->assertSessionHasErrors('permissions.1');
    }

    public function test_custom_role_can_be_deleted_but_builtins_cannot(): void
    {
        [$superadmin] = $this->users();
        $role = Role::create(['name' => 'temp', 'guard_name' => 'web']);

        $this->actingAs($superadmin)->delete('/roles/'.$role->id)->assertRedirect('/roles');
        $this->assertNull(Role::where('name', 'temp')->first());

        $this->actingAs($superadmin)->delete('/roles/'.Role::where('name', 'admin')->value('id'))
            ->assertSessionHas('error');
        $this->assertNotNull(Role::where('name', 'admin')->first());
    }

    // --- Nav gating ---------------------------------------------------------

    public function test_nav_shows_admin_surfaces_only_to_superadmin(): void
    {
        [$superadmin, $admin, $accountant] = $this->users();

        $this->actingAs($superadmin)->get('/parties')
            ->assertSee(route('users'))->assertSee(route('roles'));

        $this->actingAs($admin)->get('/parties')
            ->assertDontSee(route('users'))->assertDontSee(route('roles'));

        $this->actingAs($accountant)->get('/parties')
            ->assertDontSee(route('users'))->assertDontSee(route('roles'));
    }
}
