<?php

namespace App\Console\Commands;

use App\Services\HeadHunterVacancyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


class HhAutoSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hh:auto-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically sync vacancies from HH for all Uzbekistan cities';

    /**
     * Execute the console command.
     */
    public function handle()
    {

    }
}
