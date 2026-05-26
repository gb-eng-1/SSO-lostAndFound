<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SchoolYearArchiveReminderCommand extends Command
{
    protected $signature   = 'app:archive-reminder';
    protected $description = 'Send admin reminder to archive records for the ending school year (runs May 15).';

    public function handle(): int
    {
        $now = Carbon::now();

        // School year: June 1 – May 31. Current SY ends May 31 of this calendar year.
        $syStart = Carbon::create($now->year - 1, 6, 1);
        $syEnd   = Carbon::create($now->year, 5, 31);
        $syLabel = substr($now->year - 1, 2) . substr($now->year, 2); // e.g. "2526"

        Notification::notifyAdmin(
            'archive_reminder',
            'School-Year Archive Reminder',
            "The school year SY {$syLabel} (June 1, {$syStart->year} – May 31, {$syEnd->year}) ends soon. "
            . "Please export and archive all lost-and-found records for this period before June 1. "
            . "Use the Archive School Year export on the Inventory page to download a CSV.",
            null
        );

        $this->info("Archive reminder sent for SY {$syLabel}.");

        return Command::SUCCESS;
    }
}
