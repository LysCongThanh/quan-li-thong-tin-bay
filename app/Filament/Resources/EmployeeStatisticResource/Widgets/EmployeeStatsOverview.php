<?php

namespace App\Filament\Resources\EmployeeStatisticResource\Widgets;

use App\Models\TaskService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeStatsOverview extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '1s';

    public $employ_id;

    public function mount($employ_id)
    {
        $this->employ_id = $employ_id;
    }

    protected function getStats(): array
    {

        $query = TaskService::query()
            ->whereHas('task', function($q) {
                $q->where('pilot_id', $this->employ_id)
                    ->orWhere('support_id', $this->employ_id);
            });

        if (!empty($this->filters['date_range'])) {
            if(!empty($this->filters['date_range']['from_date'])) {
                $query->whereDate('created_at', '>=', $this->filters['date_range']['from_date']);
            }
            if(!empty($this->filters['date_range']['until_date'])) {
                $query->whereDate('created_at', '<=', $this->filters['date_range']['until_date']);
            }
        }

        return [
            Stat::make('Tổng doanh thu', number_format($query->sum('money_received'), 0) . ' VNĐ')
                ->description('Tổng tiền nhận được')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Tổng công việc', $query->distinct('task_id')->count())
                ->description('Số lượng công việc')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Hoàn thành', $query->where('status', 'completed')->count())
                ->description('Công việc hoàn thành')
                ->descriptionIcon('heroicon-m-check-circle'),

            Stat::make('Chưa hoàn thành', $query->whereIn('status', ['pending', 'in_progress'])->count())
                ->description('Công việc đang thực hiện')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
