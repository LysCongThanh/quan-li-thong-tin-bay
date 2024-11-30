<?php

namespace App\Filament\Widgets;

use App\Models\TaskService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class DashboardChart extends ChartWidget
{
    protected static ?string $heading = 'Doanh thu theo thời gian';

    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = TaskService::selectRaw('
            DATE(created_at) as date,
            SUM(money_received) as total_revenue,
            COUNT(*) as total_services
        ')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Doanh thu',
                    'data' => $data->pluck('total_revenue')->toArray(),
                    'borderColor' => '#10B981',
                    'fill' => false
                ],
                [
                    'label' => 'Số lượng dịch vụ',
                    'data' => $data->pluck('total_services')->toArray(),
                    'borderColor' => '#3B82F6',
                    'fill' => false
                ]
            ],
            'labels' => $data->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d/m'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
