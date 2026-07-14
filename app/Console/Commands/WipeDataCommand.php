<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;

/**
 * Wipe all business data while keeping users and RBAC (roles/permissions)
 * completely intact, so every existing login keeps working afterwards.
 *
 *   php artisan app:wipe-data          # asks for confirmation first
 *   php artisan app:wipe-data --force  # skips the confirmation prompt
 */
class WipeDataCommand extends Command
{
    protected $signature = 'app:wipe-data {--force : Skip the confirmation prompt}';

    protected $description = 'Delete all banks/parties/transactions/cheques/ledger data. Users and roles are kept.';

    /** Children first so this order is safe even without disabling FK checks. */
    private const TABLES = ['ledger_entries', 'transactions', 'cheques', 'parties', 'banks'];

    public function handle(): int
    {
        info('This deletes ALL rows from: '.implode(', ', self::TABLES));
        note('Users, roles, and permissions are NOT touched.');

        if (! $this->option('force') && ! confirm('Are you sure you want to permanently wipe this data?', false)) {
            $this->comment('Cancelled — nothing was deleted.');

            return self::SUCCESS;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (self::TABLES as $table) {
            DB::table($table)->truncate();
            $this->line("  truncated {$table}");
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        info('Done. Business data wiped; users/roles/permissions preserved.');

        return self::SUCCESS;
    }
}
