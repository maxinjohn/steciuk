<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('db:optimize-sqlite --light')
    ->dailyAt('03:15')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('db:optimize-sqlite')
    ->weeklyOn(0, '04:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('db:optimize-sqlite --reclaim')
    ->monthlyOn(1, '04:30')
    ->withoutOverlapping()
    ->onOneServer();
