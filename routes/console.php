<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Semiannual artist profile confirmation flow ───────────────────────────────
// Every 6 months: send initial reminder (1 Jan and 1 Jul at 08:00).
Schedule::command('artist:send-reminders')
    ->cron('0 8 1 1,7 *')
    ->withoutOverlapping()
    ->description('Semiannual profile confirmation reminders');

// Weekly: send J-7 followup to artists who haven't confirmed after 3 weeks.
Schedule::command('artist:send-reminder-followups')
    ->weeklyOn(1, '08:00')   // every Monday at 08:00
    ->withoutOverlapping()
    ->description('Semiannual profile confirmation followup reminders');

// Weekly: disable artists who have not confirmed after 4 weeks.
Schedule::command('artist:disable-inactive')
    ->weeklyOn(1, '09:00')   // every Monday at 09:00 (after followups)
    ->withoutOverlapping()
    ->description('Disable inactive artists after semiannual reminder period');
