<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Modules\Intercom\Jobs\PermitePhytosanitaryImportMBA002;
use Modules\Intercom\Jobs\ProcesarPhytosanitaryCertificateMBA002;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

$schedule = app(Schedule::class);

$schedule->job(new ProcesarPhytosanitaryCertificateMBA002)
    ->everyMinute()
    ->timezone('America/Guayaquil')
    ->withoutOverlapping()
    ->onOneServer();


$schedule->job(new PermitePhytosanitaryImportMBA002)
    ->everyTenMinutes()
    ->timezone('America/Guayaquil')
    ->withoutOverlapping()
    ->onOneServer();
