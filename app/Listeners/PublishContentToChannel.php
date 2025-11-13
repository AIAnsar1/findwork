<?php

namespace App\Listeners;

use App\Events\ContentScheduledForPublish;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PublishContentToChannel
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ContentScheduledForPublish $event): void
    {
        //
    }
}
