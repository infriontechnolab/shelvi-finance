<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Access;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\password;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

/**
 * First-time production setup — the clean install path.
 *
 *   php artisan app:setup
 *
 * Installs roles & permissions, then interactively collects the login accounts
 * (Laravel Prompts). No demo/sample data, no hard-coded passwords. Idempotent:
 * re-running updates the same users (matched by email).
 */
class SetupCommand extends Command
{
    protected $signature = 'app:setup';

    protected $description = 'Install roles/permissions and create the login accounts (interactive).';

    public function handle(): int
    {
        info('Shelvi Finance — first-time setup');

        $this->callSilent('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);
        note('Roles & permissions installed.');

        $rows = [];

        // Administrator — required.
        $rows[] = $this->makeUser(
            'admin',
            text('Administrator name', default: 'Administrator', required: true),
            text('Administrator email', required: true, validate: $this->emailRule()),
            password('Administrator password', validate: $this->passwordRule()),
        );

        // Accountant — optional.
        if (confirm('Also create an accountant account?', default: false)) {
            $rows[] = $this->makeUser(
                'accountant',
                text('Accountant name', default: 'Accountant', required: true),
                text('Accountant email', required: true, validate: $this->emailRule()),
                password('Accountant password', validate: $this->passwordRule()),
            );
        }

        // Internal owner (superadmin) — optional, hidden from the UI.
        if (confirm('Create the internal owner account? (manages users/roles, hidden in UI — keep private)', default: false)) {
            $rows[] = $this->makeUser(
                Access::SUPERADMIN,
                text('Owner name', default: 'System Owner', required: true),
                text('Owner email', required: true, validate: $this->emailRule()),
                password('Owner password', validate: $this->passwordRule()),
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        table(['Role', 'Name', 'Email'], $rows);
        info('Setup complete. Log in at /login.');

        return self::SUCCESS;
    }

    /** @return array{0:string,1:string,2:string} */
    private function makeUser(string $role, string $name, string $email, string $password): array
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password), 'is_active' => true],
        );
        $user->syncRoles($role);

        return [$role, $name, $email];
    }

    private function emailRule(): \Closure
    {
        return fn (string $value) => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : 'Enter a valid email address.';
    }

    private function passwordRule(): \Closure
    {
        return fn (string $value) => strlen($value) >= 8 ? null : 'Password must be at least 8 characters.';
    }
}
