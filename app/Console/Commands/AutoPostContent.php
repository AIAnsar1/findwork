<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;
use App\Jobs\PublishContentJob;


class AutoPostContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autopost:publis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish active resumes and open vacancies with auto_posting enabled';

    /**
     * Execute the console command.
     */
    public function handle(Nutgram $bot)
    {
        dispatch(new PublishContentJob())->onQueue('autopost');
        $this->info('✅ Job dispatched to queue: autopost');
    }
}
