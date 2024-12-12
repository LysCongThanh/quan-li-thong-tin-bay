<?php

namespace App\Filament\Widgets;

use App\Models\TaskService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class DashboardChart extends ChartWidget
{
    protected static ?string $heading = 'Doanh thu theo tháng';

    public static function canView(): bool
    {
        return auth()->user()->can('widget_DashboardChart');
    }

    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    protected function getFilters(): ?array
    {
        // Lấy tất cả các năm có trong TaskService
        $years = TaskService::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->sortDesc() // Sắp xếp theo thứ tự giảm dần để năm mới nhất lên đầu
            ->toArray();

        // Thêm 'this_year' và 'last_year' vào đầu mảng
        $filterOptions = [
            'this_year' => 'Năm nay',
            'last_year' => 'Năm trước',
        ];

        // Thêm các năm từ dữ liệu vào mảng
        foreach ($years as $year) {
            $filterOptions[(string) $year] = "Năm $year";
        }

        return $filterOptions;
    }

    protected function getData(): array
    {
        // Lấy tất cả các năm có trong dữ liệu TaskService
        $years = TaskService::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year');

        // Lấy giá trị filter từ tùy chọn (hoặc mặc định 'this_year')
        $filter = $this->filter ?? 'this_year';

        // Xử lý giá trị năm cho tùy chọn 'this_year' và 'last_year'
        // Nếu không phải là các lựa chọn này, lọc theo năm trong dữ liệu
        $year = match ($filter) {
            'this_year' => now()->year,
            'last_year' => now()->subYear()->year,
            default => $years->contains($filter) ? $filter : now()->year,
        };

        // Tổng doanh thu theo tháng cho năm đã chọn
        $data = TaskService::selectRaw('
        MONTH(created_at) as month,
        SUM(money_received) as total_revenue
    ')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_revenue', 'month');

        $monthlyRevenue = collect(range(1, 12))->mapWithKeys(function ($month) use ($data) {
            return [$month => $data->get($month, 0)];
        });

        // Doanh thu theo dịch vụ (5 dịch vụ hàng đầu)
        $topServices = TaskService::join('services', 'task_services.service_id', '=', 'services.id')
            ->selectRaw('
            services.name as service_name,
            SUM(task_services.money_received) as total_revenue
        ')
            ->whereYear('task_services.created_at', $year)
            ->groupBy('services.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->pluck('total_revenue', 'service_name');

        $serviceMonthlyData = [];
        foreach ($topServices as $serviceName => $totalRevenue) {
            $monthlyData = TaskService::join('services', 'task_services.service_id', '=', 'services.id')
                ->selectRaw('
                MONTH(task_services.created_at) as month,
                SUM(task_services.money_received) as total_revenue
            ')
                ->whereYear('task_services.created_at', $year)
                ->where('services.name', $serviceName)
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total_revenue', 'month');

            $serviceMonthlyData[] = [
                'label' => $serviceName,
                'data' => collect(range(1, 12))
                    ->mapWithKeys(fn($month) => [$month => $monthlyData->get($month, 0)])
                    ->values()
                    ->toArray(),
                'borderColor' => '#' . substr(md5($serviceName), 0, 6),
                'fill' => false,
            ];
        }

        return [
            'datasets' => array_merge([
                [
                    'label' => 'Doanh thu tổng',
                    'data' => $monthlyRevenue->values()->toArray(),
                    'borderColor' => '#10B981',
                    'fill' => false,
                ],
            ], $serviceMonthlyData),
            'labels' => $monthlyRevenue->keys()->map(fn($month) => "Tháng $month")->toArray(),
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }
}
