<?php

namespace App\Filament\Widgets;

use App\Models\Channel;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class GroupAnalytityChart extends ApexChartWidget
{
    protected static ?int $sort = 2;

    /**
     * Chart Id
     */
    protected static ?string $chartId = 'channelsAnalyticsChart';

    /**
     * Widget Title
     */
    protected static ?string $heading = 'ChannelsAnalyticsChart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     */
    protected function getOptions(): array
    {
        $data = Channel::dontCache()->select(DB::raw('DATE(last_synced_at) as date'), DB::raw('SUM(members_count) as total_members'), DB::raw('COUNT(*) as total_channels'))->groupBy('date')->orderBy('date')->get();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 350,
                'toolbar' => ['show' => false],
                'zoom' => ['enabled' => false],
            ],
            'series' => [
                [
                    'name' => 'Подписчики',
                    'data' => $data->pluck('total_members'),
                ],
                [
                    'name' => 'Количество каналов',
                    'data' => $data->pluck('total_channels'),
                ],
            ],
            'xaxis' => [
                'categories' => $data->pluck('date'),
                'labels' => [
                    'style' => [
                        'colors' => '#A1A1AA',
                        'fontSize' => '12px',
                    ],
                ],
            ],
            'colors' => ['#3B82F6', '#F59E0B'], // Синие и оранжевые линии
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
            ],
            'legend' => [
                'position' => 'top',
                'labels' => ['colors' => '#E5E7EB'],
            ],
            'grid' => [
                'borderColor' => '#374151',
                'strokeDashArray' => 4,
            ],
            'tooltip' => [
                'theme' => 'dark',
            ],
        ];
    }
}
