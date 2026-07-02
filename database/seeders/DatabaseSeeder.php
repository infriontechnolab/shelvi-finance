<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,  // permissions + roles first
            UserSeeder::class,                 // users then get roles attached
            FinanceDataSeeder::class,          // port Mock fixtures → DB
        ]);
    }
}
