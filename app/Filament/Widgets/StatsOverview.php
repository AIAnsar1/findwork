<?php

namespace App\Filament\Widgets;

use App\Models\Channel;
use App\Models\Group;
use App\Models\Resume;
use App\Models\TelegramUser;
use App\Models\Vacancy;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $channel = $this->getMainChannel();
        $group = $this->getMainGroup();

        return [
            Stat::make('Пользователи бота', number_format(TelegramUser::query()->count()))
                ->description('Количество зарегистрированных TelegramUser'),

            Stat::make('Активные резюме', number_format(
                Resume::query()->where('status', 'active')->count()
            ))->description('Сколько резюме доступны к публикации'),

            Stat::make('Активные вакансии', number_format(
                Vacancy::query()->where('status', 'open')->count()
            ))->description('Сколько вакансий доступны к публикации'),

            Stat::make('Подписчики канала', number_format($channel?->members_count ?? 0))
                ->description($this->formatDelta(Cache::get('stats.channel_members_delta'))),

            Stat::make('Участники группы', number_format($group?->members_count ?? 0))
                ->description($this->formatDelta(Cache::get('stats.group_members_delta'))),
        ];
    }

    protected function getMainChannel(): ?Channel
    {
        $channelId = config('nutgram.telegram_channel_id');

        if (! $channelId) {
            return null;
        }

        return Channel::query()->where('channel_id', $channelId)->first();
    }

    protected function getMainGroup(): ?Group
    {
        $groupId = config('nutgram.telegram_group_id');

        if (! $groupId) {
            return null;
        }

        return Group::query()->where('group_id', $groupId)->first();
    }

    protected function formatDelta(?int $delta): string
    {
        if ($delta === null) {
            return 'Нет свежих данных';
        }

        if ($delta === 0) {
            return 'Без изменений за последний час';
        }

        $sign = $delta > 0 ? '+' : '';

        return "{$sign}{$delta} за последний час";
    }
}
