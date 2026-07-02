<?php

namespace Database\Seeders;

use App\Support\Access;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles + permissions per the approved matrix.
 *  - superadmin : every permission (owner account — hidden from the UI).
 *  - admin      : everything EXCEPT user & role administration.
 *  - accountant : "operate, not administer" day-to-day subset.
 *
 * User & role management (users.*, roles.*) is superadmin-only, so those
 * permissions never appear in the visible role matrix — see App\Support\Access.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /** Every permission the app recognises. */
    private const PERMISSIONS = [
        'dashboard.view',
        'parties.view', 'parties.create', 'parties.update', 'parties.delete',
        'banks.view', 'banks.create', 'banks.update', 'banks.delete',
        'transactions.view', 'transactions.create', 'transactions.update', 'transactions.delete',
        'cheques.view', 'cheques.create', 'cheques.update', 'cheques.delete', 'cheques.verify',
        'ledger.view',
        'reports.view', 'reports.export',
        'users.view', 'users.create', 'users.update', 'users.delete',
        'roles.view', 'roles.create', 'roles.update', 'roles.delete',
    ];

    /** Accountant subset: day-to-day operation, no delete, no user/role admin, banks read-only. */
    private const ACCOUNTANT = [
        'dashboard.view',
        'parties.view', 'parties.create', 'parties.update',
        'banks.view',
        'transactions.view', 'transactions.create', 'transactions.update',
        'cheques.view', 'cheques.create', 'cheques.update', 'cheques.verify',
        'ledger.view',
        'reports.view', 'reports.export',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Secret owner role: every permission, hidden from the UI.
        $superadmin = Role::firstOrCreate(['name' => Access::SUPERADMIN, 'guard_name' => 'web']);
        $superadmin->syncPermissions(self::PERMISSIONS);

        // Admin: everything except user & role administration.
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($this->adminPermissions());

        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions(self::ACCOUNTANT);
    }

    /** Admin = all permissions minus the superadmin-only user/role groups. */
    private function adminPermissions(): array
    {
        return array_values(array_filter(
            self::PERMISSIONS,
            fn (string $p) => ! in_array(explode('.', $p)[0], Access::HIDDEN_GROUPS, true),
        ));
    }
}
