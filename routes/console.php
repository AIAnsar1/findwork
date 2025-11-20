<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\PublishContentJob;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new PublishContentJob())
    ->timezone('Asia/Tashkent')
    ->everyFiveMinutes()
    ->onOneServer();



// dailyAt('7:00')