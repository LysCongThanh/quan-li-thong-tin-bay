<?php

namespace App\Filament\Widgets;

use App\Models\TaskService;
use Filament\Widgets\ChartWidget;

class TaskServiceChart extends ChartWidget
{

    protected static ?string $heading = 'Tổng số công việc theo trạng thái';

    public static function canView(): bool
    {
        return auth()->user()->can('widget_TaskServiceChart');
    }

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Lấy dữ liệu trạng thái và số lượng nhiệm vụ theo trạng thái
        $statuses = TaskService::selectRaw('
        status, COUNT(*) as count
    ')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Định nghĩa bản đồ trạng thái sang tiếng Việt
        $statusTranslations = [
            'completed' => 'Hoàn thành',
            'in_progress' => 'Đang tiến hành',
            'pending' => 'Chờ xử lý',
            'cancelled' => 'Đã huỷ'
        ];

        $translatedStatuses = $statuses->mapWithKeys(function ($count, $status) use ($statusTranslations) {
            return [$statusTranslations[$status] => $count];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Số nhiệm vụ theo trạng thái',
                    'data' => $translatedStatuses->values()->toArray(),
                    'backgroundColor' => [
                        '#10B981', '#F59E0B', '#EF4444', '#3B82F6', '#6366F1',
                    ],
                ],
            ],
            'labels' => $translatedStatuses->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }


}
