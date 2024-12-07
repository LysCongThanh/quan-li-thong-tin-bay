<?php

namespace App\Filament\Widgets;

use App\Models\TaskService;
use Filament\Widgets\ChartWidget;

class EmployeeCompleteTaskChart extends ChartWidget
{

    protected static ?string $heading = 'Tổng doanh thu theo nhân viên thực hiện nhiệm vụ';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $pilotRevenue = TaskService::join('tasks', 'task_services.task_id', '=', 'tasks.id')
            ->join('users', 'tasks.pilot_id', '=', 'users.id')
            ->selectRaw('users.name as pilot_name, SUM(task_services.money_received) as total_revenue')
            ->groupBy('users.name')
            ->orderByDesc('total_revenue')
            ->pluck('total_revenue', 'pilot_name');

        return [
            'datasets' => [
                [
                    'label' => 'Doanh thu theo nhân viên (Phi công)',
                    'data' => $pilotRevenue->values()->toArray(),
                    'backgroundColor' => [
                        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#6366F1',
                    ],
                ],
            ],
            'labels' => $pilotRevenue->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
