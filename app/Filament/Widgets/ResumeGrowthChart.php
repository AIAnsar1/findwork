<?php

namespace App\Filament\Widgets;

use App\Models\Resume;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ResumeGrowthChart extends ApexChartWidget
{
    protected static ?int $sort = 3;

    /**
     * Chart Id
     */
    protected static ?string $chartId = 'postsGrowthChart';

    /**
     * Widget Title
     */
    protected static ?string $heading = 'Рост публикаций';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     */
    protected function getOptions(): array
    {
        $posts = Resume::select(DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $articles = Resume::select(DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $ads = Resume::select(DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Собираем общий набор месяцев (пусть будет отсортирован)
        $months = collect(array_keys($posts))
            ->merge(array_keys($articles))
            ->merge(array_keys($ads))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Функция для получения значения по месяцу с дефолтом 0
        $mapValues = function (array $source) use ($months) {
            return array_map(fn ($m) => $source[$m] ?? 0, $months);
        };

        return [
            'chart' => ['type' => 'line', 'height' => 350],
            'series' => [
                ['name' => 'Посты', 'data' => $mapValues($posts)],
                ['name' => 'Статьи', 'data' => $mapValues($articles)],
                ['name' => 'Рекламы', 'data' => $mapValues($ads)],
            ],
            'xaxis' => [
                'categories' => $months,
            ],
            'colors' => ['#3B82F6', '#F59E0B', '#10B981'],
            'stroke' => ['curve' => 'smooth', 'width' => 3],
            'tooltip' => ['theme' => 'dark'],
        ];
    }
}
