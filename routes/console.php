<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rekap EIS diperbarui tiap jam (butuh cron yang menjalankan schedule:run).
Schedule::command('eis:sync')->hourly()->withoutOverlapping();
