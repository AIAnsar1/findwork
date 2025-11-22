<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use SergiX44\Nutgram\Nutgram;
use App\Models\{Channel, Group};
use Illuminate\Support\Facades\Log;

class UpdateChannelStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle(Nutgram $bot): void
    {
        Log::info('📊 Starting channel/group stats update...');

        $this->updateChannels($bot);
        $this->updateGroups($bot);

        Log::info('✅ Channel/group stats updated');
    }

    protected function updateChannels(Nutgram $bot): void
    {
        $channels = Channel::whereNotNull('channel_id')->get();

        foreach ($channels as $channel) {
            try {
                $chat = $bot->getChat(chat_id: $channel->channel_id);

                $oldCount = $channel->members_count;
                $newCount = $chat->members_count ?? $oldCount;

                $channel->fill([
                    'title' => $chat->title ?? $channel->title,
                    'description' => $chat->description ?? $channel->description,
                    'invite_link' => $chat->invite_link ?? $channel->invite_link,
                    'bot_is_admin' => $this->isBotAdmin($chat),
                    'members_count' => $newCount,
                    'last_synced_at' => now(),
                ])->save();

                if ($oldCount !== $newCount) {
                    Log::info("📈 Channel {$channel->title} subscribers: {$oldCount} → {$newCount}");
                }

            } catch (\Throwable $e) {
                Log::warning("⚠️ Failed to update channel {$channel->channel_id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function updateGroups(Nutgram $bot): void
    {
        $groups = Group::whereNotNull('group_id')->get();

        foreach ($groups as $group) {
            try {
                $chat = $bot->getChat(chat_id: $group->group_id);

                $oldCount = $group->members_count;
                $newCount = $chat->members_count ?? $oldCount;

                $group->fill([
                    'title' => $chat->title ?? $group->title,
                    'description' => $chat->description ?? $group->description,
                    'invite_link' => $chat->invite_link ?? $group->invite_link,
                    'bot_is_admin' => $this->isBotAdmin($chat),
                    'members_count' => $newCount,
                    'last_synced_at' => now(),
                ])->save();

                if ($oldCount !== $newCount) {
                    Log::info("📈 Group {$group->title} members: {$oldCount} → {$newCount}");
                }

            } catch (\Throwable $e) {
                Log::warning("⚠️ Failed to update group {$group->group_id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function isBotAdmin($chat): bool
    {
        if (!isset($chat->permissions)) return false;

        $perms = $chat->permissions;
        return $perms->can_post_messages ?? $perms->can_send_messages ?? false;
    }
}
