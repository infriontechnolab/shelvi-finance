<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardEmptyStateTest extends TestCase
{
    use RefreshDatabase;

    /** A clean install (roles only, no finance data) must not divide by zero. */
    public function test_dashboard_renders_on_an_empty_database(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@empty.test',
            'password' => bcrypt('secret-pass-1'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/')->assertOk();
    }
}
