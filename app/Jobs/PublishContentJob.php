<?php

namespace App\Jobs;

use App\Helpers\FormatForChannelTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\{Resume, Vacancy};
use Carbon\Carbon;
use SergiX44\Nutgram\Nutgram;

class PublishContentJob implements ShouldQueue
{
    use Queueable, FormatForChannelTrait;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(Nutgram $bot): void
    {
        $twelveHoursAgo = Carbon::now()->subHours(12);

        $resumes = Resume::where('auto_posting', true)
            ->where('status', 'active')
            ->where(function ($query) use ($twelveHoursAgo) {
                $query->whereNull('last_posted_at')
                    ->orWhere('last_posted_at', '<=', $twelveHoursAgo);
            })
            ->get();

        foreach ($resumes as $resume) {
            $this->postToChannel($bot, 'resume', $resume, $bot);
            $resume->update(['last_posted_at' => now()]);
        }

        $vacancies = Vacancy::where('auto_posting', true)
            ->where('status', 'open')
            ->where(function ($query) use ($twelveHoursAgo) {
                $query->whereNull('last_posted_at')
                    ->orWhere('last_posted_at', '<=', $twelveHoursAgo);
            })
            ->get();

        foreach ($vacancies as $vacancy) {
            $this->postToChannel($bot, 'vacancy', $vacancy, $bot);
            $vacancy->update(['last_posted_at' => now()]);
        }
    }
}