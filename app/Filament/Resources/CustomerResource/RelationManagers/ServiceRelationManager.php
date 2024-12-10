<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceRelationManager extends RelationManager
{
    protected static string $relationship = 'services';
    protected static ?string $recordTitleAttribute = 'service_name';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->heading('Lịch sử sử dụng dịch vụ')
            ->recordTitleAttribute('service_name')
            ->columns([
                Tables\Columns\TextColumn::make('service_name')
                    ->label('Tên dịch vụ'),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->badge()
                    ->label('Tổng số lượng')
                    ->formatStateUsing(fn($record) =>
                    "{$record->total_quantity} {$record->service_unit}"),
                Tables\Columns\TextColumn::make('money')
                    ->badge()
                    ->money('VND')
                    ->label('Tổng tiền')
                    ->getStateUsing(function($record) {
                        return $record->total_money_received ?: ($record->total_quantity * $record->price);
                    })
            ])
            ->modifyQueryUsing(function ($query) {
                return $query
                    ->select([
                        'task_services.id',
                        'task_services.service_name',
                        'task_services.service_unit',
                        \DB::raw('SUM(task_services.quantity) as total_quantity'),
                        \DB::raw('SUM(task_services.money_received) as total_money_received'),
                        'services.price'
                    ])
                    ->groupBy('task_services.id', 'task_services.service_name', 'task_services.service_unit', 'services.price');
            });
    }
}
