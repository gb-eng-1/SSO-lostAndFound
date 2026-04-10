<?php

namespace App\Console\Commands;

use App\Services\AutoMatchService;
use App\Services\MatchScoringService;
use Illuminate\Console\Command;

/**
 * Scheduled batch matching pass.
 * Run via: php artisan matches:run
 * Scheduled every 15 minutes in routes/console.php.
 */
class RunAutoMatchCommand extends Command
{
    protected $signature   = 'matches:run {--dry-run : Show what would be linked without making changes}';
    protected $description = 'Auto-match unlinked lost reports to the best-scoring found items.';

    public function handle(): int
    {
        $service = new AutoMatchService(new MatchScoringService());

        if ($this->option('dry-run')) {
            $this->info('Dry-run mode — no changes will be saved.');
        }

        $linked = $service->runAll();

        $this->info("Auto-matching complete: {$linked} pair(s) linked.");

        return self::SUCCESS;
    }
}
