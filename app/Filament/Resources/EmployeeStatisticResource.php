<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeStatisticResource\Pages;
use App\Filament\Resources\EmployeeStatisticResource\Widgets\EmployeeRevenueStats;
use App\Filament\Resources\EmployeeStatisticResource\Widgets\EmployeeStatsOverview;
use App\Models\TaskService;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// EmployeeStatisticResource.php
class EmployeeStatisticResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Thống kê nhân viên';
    protected static ?string $modelLabel = 'Thống kê nhân viên';

    public static function getWidgets(): array
    {
        return [
            EmployeeStatsOverview::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Thống kê doanh thu nhân viên')
            ->description('Thống kê chi tiết doanh thu và công việc của từng nhân viên')
            ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('roles', fn($q) => $q->where('name', 'Nhân viên')
            ))
            ->columns([
                TextColumn::make('name')
                    ->label('Tên nhân viên')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_tasks')
                    ->label('Số công việc')
                    ->getStateUsing(function ($record) {
                        return count($record->pilotTasks->toArray());
                    })
                    ->alignCenter()
                    ->color('success')
                    ->badge(),

                TextColumn::make('total_service_money')
                    ->label('Tổng định giá')
                    ->description('Theo đơn giá gốc')
                    ->money('VND')
                    ->getStateUsing(function ($record) {
                        return TaskService::whereHas('task', function ($q) use ($record) {
                            $q->where('pilot_id', $record->id)
                                ->orWhere('support_id', $record->id);
                        })->sum(DB::raw('service_price * quantity'));
                    })
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('revenue')
                    ->label('Doanh thu thực')
                    ->description('Số tiền nhận được')
                    ->money('VND')
                    ->getStateUsing(function ($record) {
                        return TaskService::whereHas('task', function ($q) use ($record) {
                            $q->where('pilot_id', $record->id)
                                ->orWhere('support_id', $record->id);
                        })->sum('money_received');
                    })
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('name')
            ->toggleColumnsTriggerAction(
                fn(\Filament\Tables\Actions\Action $action) => $action
                    ->button()
            )
            ->recordUrl(fn($record) => static::getUrl('details', ['record' => $record]))
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('Xem chi tiết')
                    ->icon('heroicon-o-eye')
                    ->tooltip('Xem chi tiết thống kê')
                    ->button()
                    ->url(fn($record) => static::getUrl('details', ['record' => $record]))
            ])
            ->poll('10s')
            ->striped()
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeStatistics::route('/'),
            'details' => Pages\EmployeeTaskServiceList::route('/{record}/details'),
        ];
    }
}
