<?php

namespace App\Filament\Widgets;

use App\Models\TaskService;
use Filament\Widgets\ChartWidget;

class RevenueFromCustomerChart extends ChartWidget
{

    protected static ?string $heading = 'Top doanh thu từ khách hàng cao nhất';

    public static function canView(): bool
    {
        return auth()->user()->can('widget_RevenueFromCustomerChart');
    }

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $customerRevenue = TaskService::join('tasks', 'task_services.task_id', '=', 'tasks.id')
            ->join('customers', 'tasks.customer_id', '=', 'customers.id')
            ->selectRaw('customers.name as customer_name, SUM(task_services.money_received) as total_revenue')
            ->groupBy('customers.name')
            ->orderByDesc('total_revenue')
            ->limit(10) // Lấy 10 khách hàng hàng đầu
            ->pluck('total_revenue', 'customer_name');

        return [
            'datasets' => [
                [
                    'label' => 'Top khách hàng theo doanh thu',
                    'data' => $customerRevenue->values()->toArray(),
                    'backgroundColor' => [
                        '#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#6366F1',
                    ],
                ],
            ],
            'labels' => $customerRevenue->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
