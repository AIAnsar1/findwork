<?php

namespace App\Jobs;

use App\Models\HeadHunterVacancy;
use App\Services\HeadHunterVacancyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class SyncHhVacanciesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(HeadHunterVacancyService $service): void
    {
        Log::info('🔄 Starting HH vacancies sync job');

        try {
            $results = $service->runAutoSync();

            Log::info('✅ HH sync job completed', $results);

        } catch (\Exception $e) {
            Log::error('❌ HH sync job failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        Log::error('SyncHhVacanciesJob failed: ' . $exception->getMessage());
    }
}
