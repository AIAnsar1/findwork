<?php

namespace App\Console\Commands;

use App\Jobs\PublishContentJob;
use Illuminate\Console\Command;

class PublishContentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:publish-content-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches the PublishContentJob to re-post resumes and vacancies.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        PublishContentJob::dispatch();
        $this->info('PublishContentJob dispatched successfully!');
    }
}
