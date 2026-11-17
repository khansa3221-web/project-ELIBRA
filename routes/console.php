<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengingat tenggat & penandaan terlambat setiap pagi (butuh: php artisan schedule:work / cron schedule:run).
// Tanpa scheduler pun, pengingat tetap jalan saat dashboard dibuka.
\Illuminate\Support\Facades\Schedule::command('elibra:reminders')->dailyAt('07:00');
