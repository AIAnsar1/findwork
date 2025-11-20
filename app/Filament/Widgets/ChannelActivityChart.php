<?php

namespace App\Filament\Widgets;

use App\Models\Channel;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ChannelActivityChart extends ApexChartWidget
{
    protected static ?int $sort = 1;

    /**
     * Chart Id
     */
    protected static ?string $chartId = 'channelActivityChart';

    /**
     * Widget Title
     */
    protected static ?string $heading = 'Активность каналов';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     */
    protected function getOptions(): array
    {
        $channels = Channel::dontCache()->select('title', 'members_count')->orderByDesc('members_count')->take(5)->pluck('members_count', 'title');

        return [
            'chart' => ['type' => 'donut', 'height' => 350],
            'series' => array_values($channels->toArray()),
            'labels' => $channels->keys(),
            'colors' => ['#3B82F6', '#EF4444', '#F59E0B', '#10B981', '#8B5CF6'],
        ];
    }
}
