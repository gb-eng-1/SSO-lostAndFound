<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time or maintenance wipe of all notification rows.
 * Run: php artisan notifications:truncate
 */
class TruncateNotificationsCommand extends Command
{
    protected $signature = 'notifications:truncate {--force : Skip confirmation}';

    protected $description = 'Delete all rows from the notifications table (fresh start; new events repopulate).';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will remove ALL notifications for every admin and student. Continue?')) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        DB::table('notifications')->truncate();
        $this->info('Notifications table truncated.');

        return self::SUCCESS;
    }
}
