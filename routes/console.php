<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('services:send-missing-image-reminders')
    ->dailyAt('08:00')
    ->timezone(config('app.timezone', 'Europe/Bucharest'))
    ->withoutOverlapping(60);
