<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\{PublishContentJob, UpdateChannelStatsJob, SyncHhVacanciesJob};


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');




Schedule::job(new PublishContentJob())->timezone('Asia/Tashkent')->dailyAt('7:00')->onOneServer();
Schedule::job(new UpdateChannelStatsJob())->hourly()->onOneServer();
Schedule::job(new SyncHhVacanciesJob())->hourly()->onOneServer();






























