<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule scholarship system maintenance
Schedule::command('cycles:manage')->dailyAt('00:30')->withoutOverlapping();
Schedule::command('cycles:manage --force')->yearly()->monthlyOn(7, 1, '01:00'); // July 1st reset
