<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('rewards:decrement-count-days')->daily();
Schedule::command('requests:delete-processed')->hourly();
Schedule::command('requests:delete-stale-pending')->daily();
