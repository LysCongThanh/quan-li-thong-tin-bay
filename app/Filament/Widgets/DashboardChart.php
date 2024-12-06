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

    protected function getFilters(): ?array
    {
        return [
            '7_days' => '7 ngày gần đây',
            '10_days' => '10 ngày gần đây',
            '30_days' => '30 ngày gần đây',
            '90_day' => '90 ngày gần đây'
        ];
    }

    protected function getData(): array
    {
        // Lấy bộ lọc hiện tại
        $filter = $this->filter ?? '90_days';

        // Xác định số ngày dựa trên bộ lọc
        $days = match ($filter) {
            '7_days' => 7,
            '10_days' => 10,
            '30_days' => 30,
            '90_day' => 90,
            default => 90,  
        };

        // Truy vấn dữ liệu
        $data = TaskService::selectRaw('
        DATE(created_at) as date,
        SUM(money_received) as total_revenue,
        COUNT(*) as total_services
    ')
            ->whereDate('created_at', '>=', now()->subDays($days))
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
