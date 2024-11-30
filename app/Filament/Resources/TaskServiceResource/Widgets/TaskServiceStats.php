<?php

namespace App\Filament\Resources\TaskServiceResource\Widgets;

use App\Models\TaskService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TaskServiceStats extends StatsOverviewWidget
{
    public ?string $task_id = null;

    public function mount(?string $task_id = null)
    {
        $this->task_id = $task_id;
    }

    protected function getStats(): array
    {
        if (!$this->task_id) {
            return $this->getEmptyStats();
        }

        $baseQuery = TaskService::query()->where('task_id', $this->task_id);

        $totalServices = $baseQuery->clone()->count();
        $totalMoney = $baseQuery->clone()->sum('money_received');

        $totalCompleted = $baseQuery->clone()->where('status', 'completed')->count();
        $completionRate = $totalServices > 0 ? round(($totalCompleted / $totalServices) * 100) : 0;

        $statusCounts = $baseQuery->clone()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            Stat::make('Tổng dịch vụ', $totalServices)
                ->description('Số lượng dịch vụ đã thực hiện')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Tổng thu', number_format($totalMoney) . ' VND')
                ->description('Tổng số tiền đã thu')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Trạng thái', $completionRate . '%')
                ->description('Tỷ lệ hoàn thành')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Chi tiết trạng thái', sprintf(
                'Hoàn thành: %d, Đang xử lý: %d, Chờ xử lý: %d',
                $statusCounts['completed'] ?? 0,
                $statusCounts['in_progress'] ?? 0,
                $statusCounts['pending'] ?? 0
            ))
                ->description('Phân loại theo trạng thái')
                ->descriptionIcon('heroicon-m-flag')
                ->color('warning'),
        ];
    }

    protected function getEmptyStats(): array
    {
        return [
            Stat::make('Tổng dịch vụ', 0)
                ->description('Số lượng dịch vụ đã thực hiện')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Tổng thu', '0 VND')
                ->description('Tổng số tiền đã thu')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Trạng thái', '0%')
                ->description('Tỷ lệ hoàn thành')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Chi tiết trạng thái', 'Hoàn thành: 0, Đang xử lý: 0, Chờ xử lý: 0')
                ->description('Phân loại theo trạng thái')
                ->descriptionIcon('heroicon-m-flag')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
