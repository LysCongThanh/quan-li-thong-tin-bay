<?php

namespace App\Filament\Resources\EmployeeStatisticResource\Pages;

use App\Filament\Resources\EmployeeStatisticResource;
use App\Filament\Resources\EmployeeStatisticResource\Widgets\EmployeeStatsOverview;
use App\Models\TaskService;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Prompts\SearchPrompt;

class EmployeeTaskServiceList extends ListRecords
{
    protected static string $resource = EmployeeStatisticResource::class;

    public ?Model $employee = null;

    public function mount(): void
    {
        $this->employee = User::find(request('record'));
    }

    /**
     * @return string|\Illuminate\Contracts\Support\Htmlable
     */
    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        $employeeName = $this->employee->name;
        return 'Thống kê chi tiết nhân viên: ' . $employeeName;
    }

    protected function getTableStats()
    {
        $baseQuery = TaskService::query()
            ->whereHas('task', function ($q) {
                $q->where('pilot_id', $this->employee->id)
                    ->orWhere('support_id', $this->employee->id);
            });

        $totalQuery = clone $baseQuery;
        $statusQuery = clone $baseQuery;

        if ($this->tableFilters['date_range'] ?? null) {
            $filters = $this->tableFilters['date_range'];
            if ($filters['from_date'] ?? null) {
                $baseQuery->whereDate('created_at', '>=', $filters['from_date']);
                $totalQuery->whereDate('created_at', '>=', $filters['from_date']);
                $statusQuery->whereDate('created_at', '>=', $filters['from_date']);
            }
            if ($filters['until_date'] ?? null) {
                $baseQuery->whereDate('created_at', '<=', $filters['until_date']);
                $totalQuery->whereDate('created_at', '<=', $filters['until_date']);
                $statusQuery->whereDate('created_at', '<=', $filters['until_date']);
            }
        }

        return [
            'total_revenue' => $totalQuery->sum('money_received'),
            'total_tasks' => $totalQuery->distinct('task_id')->count(),
            'completed_tasks' => $statusQuery->where('status', 'completed')->count(),
            'pending_tasks' => $statusQuery->whereIn('status', ['pending', 'in_progress'])->count()
        ];
    }

    public function table(Table $table): Table
    {
        $stats = $this->getTableStats();

//        dd(TaskService::query()
//            ->whereHas('task', function($query) {
//                $query->where('pilot_id', $this->employee->id)
//                    ->orWhere('support_id', $this->employee->id);
//            })->with('task')->get());

        return $table
            ->heading('Danh sách công việc của nhân viên')
            ->query(
                TaskService::query()
                    ->selectRaw('task_services.*, (task_services.quantity * task_services.service_price) AS total_price')
                    ->whereHas('task', function ($query) {
                        $query->where('pilot_id', $this->employee->id)
                            ->orWhere('support_id', $this->employee->id);
                    })
            )
            ->columns([
                TextColumn::make('task.name')
                    ->searchable()
                    ->label('Tên công việc')
                    ->tooltip('Tên công việc được giao')
                    ->toggleable(),

                TextColumn::make('service_name')
                    ->label('Tên dịch vụ')
                    ->description(fn($record) => collect([
                        "Số lượng: {$record->quantity} " . ($record->service_unit ?? ''),
                        "Đơn giá: " . number_format($record->service_price ?? 0, 0, ',', '.') . " VNĐ"
                    ])->join(', ')
                    )
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('task.pilot.name')
                    ->label('Phi công')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-user-circle'),

                TextColumn::make('task.support.name')
                    ->label('Hỗ trợ')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-users'),

                TextColumn::make('total_price')
                    ->label('Tổng tiền')
                    ->sortable()
                    ->tooltip('Tổng tiền = Số lượng × Đơn giá')
                    ->money('VND')
                    ->color('success')
                    ->alignCenter()
                    ->summarize([
                        Sum::make()
                            ->label('Tổng cộng')
                            ->money('VND'),
                        Average::make()
                            ->label('Trung bình')
                            ->money('VND'),
                    ])
                    ->toggleable(),

                TextColumn::make('money_received')
                    ->label('Số tiền nhận')
                    ->tooltip('Số tiền thực tế nhận được')
                    ->money('VND')
                    ->color('primary')
                    ->alignCenter()
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('Tổng thực nhận')
                            ->money('VND'),
                        Average::make()
                            ->label('Trung bình')
                            ->money('VND'),
                    ])
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->alignCenter()
                    ->colors([
                        'warning' => fn($state) => $state === 'pending',
                        'primary' => fn($state) => $state === 'in_progress',
                        'success' => fn($state) => $state === 'completed',
                        'danger' => fn($state) => $state === 'cancelled',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'Chờ xử lý',
                        'in_progress' => 'Đang thực hiện',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                        default => $state
                    })
                    ->toggleable(),

                TextColumn::make('reporter.name')
                    ->label('Người báo cáo')
                    ->icon('heroicon-o-user')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('task.name')
                    ->label('Nhóm theo công việc')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn($record) => 'Công việc: ' . $record->task->name),
                Group::make('status')
                    ->label('Nhóm theo trạng thái')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn($record) => match($record->status) {
                            'pending' => 'Chờ xử lý',
                            'in_progress' => 'Đang thực hiện',
                            'completed' => 'Hoàn thành',
                            'cancelled' => 'Đã hủy',
                            default => $record->status
                        })
            ])
            ->defaultGroup('task.name')
            ->groupRecordsTriggerAction(
                fn(\Filament\Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Nhóm dữ liệu')
            )
            ->groupingSettingsHidden(false)
            ->headerActions([
                \Filament\Tables\Actions\Action::make('stats')
                    ->label('Xem chi tiết thống kê')
                    ->icon('heroicon-o-chart-bar')
                    ->color('success')
                    ->modalIcon('heroicon-o-chart-bar')
                    ->modalHeading('Thống kê chi tiết')
                    ->modalDescription(function () {
                        $fromDate = $this->tableFilters['date_range']['from_date'] ?? null;
                        $untilDate = $this->tableFilters['date_range']['until_date'] ?? null;

                        $dateRange = '';
                        if ($fromDate && $untilDate) {
                            $dateRange = sprintf(
                                'Từ ngày %s đến ngày %s',
                                Carbon::parse($fromDate)->format('d/m/Y'),
                                Carbon::parse($untilDate)->format('d/m/Y')
                            );
                        }

                        return collect([
                            'Nhân viên: ' . $this->employee->name,
                            $dateRange
                        ])->filter()->join(' - ');
                    })
                    ->modalContent(view(
                        'filament.employee-stats',
                        ['stats' => $stats]
                    ))
                    ->button()
                    ->modalWidth('xl')
                    ->extraAttributes([
                        'class' => 'font-medium'
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('details')
                    ->label('Chi tiết')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->slideOver()
                    ->modalWidth('lg')
                    ->modalHeading(fn($record) => 'Thông tin chi tiết công việc: ' . $record->task->name)
                    ->modalDescription(fn($record) => 'Báo cáo ngày: ' . $record->created_at->format('d/m/Y H:i'))
                    ->modalContent(function ($record) {
                        return view('filament.task-stat', [
                            'record' => $record
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->button()
                    ->tooltip('Xem chi tiết công việc')
                    ->extraAttributes([
                        'class' => 'font-medium'
                    ])
            ])
            ->toggleColumnsTriggerAction(
                fn (\Filament\Tables\Actions\Action $action) => $action
                    ->button()
            )
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from_date')
                            ->label('Từ ngày')
                            ->placeholder('DD/MM/YYYY')
                            ->displayFormat('d/m/Y')
                            ->native(false),
                        DatePicker::make('until_date')
                            ->label('Đến ngày')
                            ->placeholder('DD/MM/YYYY')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                    ])
                    ->columnSpanFull()
                    ->columns(2)
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from_date'] ?? null) {
                            $indicators[] = Indicator::make('Từ ngày: ' . Carbon::parse($data['from_date'])->format('d/m/Y'))
                                ->removeField('from_date');
                        }

                        if ($data['until_date'] ?? null) {
                            $indicators[] = Indicator::make('Đến ngày: ' . Carbon::parse($data['until_date'])->format('d/m/Y'))
                                ->removeField('until_date');
                        }

                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn($q, $date) => $q->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['until_date'],
                                fn($q, $date) => $q->whereDate('created_at', '<=', $date)
                            );
                    })
            ],
                layout: FiltersLayout::Dropdown)
            ->filtersFormColumns(2)
            ->filtersTriggerAction(
                fn (\Filament\Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Bộ lọc')
                    ->color('gray')
                    ->icon('heroicon-o-funnel')
            )
            ->filtersApplyAction(
                fn (\Filament\Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Áp dụng')
                    ->color('primary')
            );
    }
}
