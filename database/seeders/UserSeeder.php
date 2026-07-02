<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Access;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Default users: one admin, one accountant. Passwords are dev defaults —
 * change in any real deployment.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Secret owner account — never shown in any management screen.
        $superadmin = User::updateOrCreate(
            ['email' => 'owner@shelvi.test'],
            ['name' => 'System Owner', 'password' => Hash::make('password'), 'is_active' => true],
        );
        $superadmin->syncRoles(Access::SUPERADMIN);

        $admin = User::updateOrCreate(
            ['email' => 'admin@shelvi.test'],
            ['name' => 'Shelvi Admin', 'password' => Hash::make('password'), 'is_active' => true],
        );
        $admin->syncRoles('admin');

        $accountant = User::updateOrCreate(
            ['email' => 'accountant@shelvi.test'],
            ['name' => 'Shelvi Accountant', 'password' => Hash::make('password'), 'is_active' => true],
        );
        $accountant->syncRoles('accountant');
    }
}
