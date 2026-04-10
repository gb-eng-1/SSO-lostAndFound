<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run auto-matching every 15 minutes as a scheduled backup.
// Event-driven matching is the primary trigger (after encode / lost-report create).
Schedule::command('matches:run')->everyFifteenMinutes();
