<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use SergiX44\Nutgram\Nutgram;
use App\Models\{Resume, Vacancy};
use Carbon\Carbon;
use App\Helpers\FormatForChannelTrait;
use Illuminate\Support\Facades\Log;

class PublishContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FormatForChannelTrait;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $bot = app(Nutgram::class);
        $this->publishQueueResume($bot);
        $this->publishQueueVacancy($bot);
    }

    protected function publishQueueResume(Nutgram $bot)
    {
        $resumes = Resume::where('auto_posting', true)->where('status', 'active')->where(function ($query) {
            $query->whereNull('last_posted_at')->orWhere('last_posted_at', '<', Carbon::now()->subMinutes(3));
        })->limit(5)->get();
        // $resumes = Resume::where('auto_posting', true)->limit(2)->get();

        foreach ($resumes as $resume) {
            try {
                $this->postToChannel($bot, 'resume', $resume);
                $resume->update(['last_posted_at' => now()]);

                Log::info("✅ Published resume #{$resume->id} for user {$resume->telegramUser->user_id}");
            } catch (\Throwable $e) {
                Log::error("❌ Failed to publish resume #{$resume->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function publishQueueVacancy(Nutgram $bot)
    {
        $vacancies = Vacancy::where('auto_posting', true)->where('status', 'open')->where(function ($query) {
           $query->whereNull('last_posted_at')->orWhere('last_posted_at', '<', Carbon::now()->subMinutes(3));
        })->limit(5)->get();

        // $vacancies = Vacancy::where('auto_posting', true)->limit(2)->get();

        foreach ($vacancies as $vacancy) {
            try {
                $this->postToChannel($bot, 'vacancy', $vacancy);
                $vacancy->update(['last_posted_at' => now()]);

                Log::info("✅ Published vacancy #{$vacancy->id} for user {$vacancy->telegramUser->user_id}");
            } catch (\Throwable $e) {
                Log::error("❌ Failed to publish vacancy #{$vacancy->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
