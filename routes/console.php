<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <--- Am adăugat acest import important

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 |=======================================================
 |   PROGRAMARE SARCINI (SCHEDULER)
 |=======================================================
 | Aici definim ca procesarea geolocației să ruleze automat
 | o dată pe oră.
 */

Schedule::command('visits:process-geo')->hourly();