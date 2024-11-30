<?php

namespace App\Filament\Widgets;

use App\Models\Service;
use App\Models\Task;
use App\Models\TaskService;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Thống kê Task
        $totalTasks = Task::count();
        $taskStats = Task::selectRaw('
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_tasks,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as weekly_tasks
        ')->first();

        // Thống kê TaskService
        $taskServiceStats = TaskService::selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN status = "completed" THEN 1 END) as completed,
            COUNT(CASE WHEN status = "in_progress" THEN 1 END) as in_progress,
            COUNT(CASE WHEN status = "pending" THEN 1 END) as pending,
            SUM(money_received) as total_revenue,
            AVG(money_received) as avg_revenue
        ')->first();

        // Thống kê Service
        $serviceStats = Service::selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_services
        ')->first();

        // Thống kê User theo role
        $userStats = User::selectRaw('
            COUNT(DISTINCT users.id) as total_users,
            SUM(CASE WHEN roles.name = "Nhân viên" THEN 1 ELSE 0 END) as total_employees
        ')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->first();

        // Top dịch vụ được sử dụng nhiều nhất
        $topServices = TaskService::select('service_id', DB::raw('COUNT(*) as usage_count'))
            ->with('service:id,name')
            ->groupBy('service_id')
            ->orderByDesc('usage_count')
            ->limit(3)
            ->get();

        return [
            // Thống kê công việc
            Stat::make('Tổng số công việc', $totalTasks)
                ->description($taskStats->weekly_tasks . ' công việc trong tuần này')
                ->descriptionIcon('heroicon-m-briefcase')
                ->chart([7, 3, 4, 5, 6, 3, $taskStats->weekly_tasks])
                ->color('primary'),

            // Thống kê doanh thu
            Stat::make('Tổng doanh thu', number_format($taskServiceStats->total_revenue) . ' VND')
                ->description('Trung bình: ' . number_format($taskServiceStats->avg_revenue) . ' VND/dịch vụ')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([4, 8, 3, 6, 5, 3, round($taskServiceStats->avg_revenue / 100000)])
                ->color('success'),

            // Thống kê trạng thái dịch vụ
            Stat::make('Trạng thái dịch vụ', $taskServiceStats->total)
                ->description(sprintf('Hoàn thành: %d | Đang xử lý: %d | Chờ: %d',
                    $taskServiceStats->completed,
                    $taskServiceStats->in_progress,
                    $taskServiceStats->pending))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),

            // Top dịch vụ
            Stat::make('Top dịch vụ sử dụng', $topServices->pluck('service.name')->first() ?? 'N/A')
                ->description(implode(' | ', array_map(
                    fn($service) => $service['service']['name'] . ': ' . $service['usage_count'],
                    $topServices->take(3)->toArray()
                )))
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
