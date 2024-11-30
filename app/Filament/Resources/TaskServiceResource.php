<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskServiceResource\Pages;
use App\Filament\Resources\TaskServiceResource\RelationManagers;
use App\Filament\Resources\TaskServiceResource\Widgets\TaskServiceStats;
use App\Models\Task;
use App\Models\TaskService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskServiceResource extends Resource
{
    protected static ?string $model = TaskService::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getWidgets(): array
    {
        return [
            TaskServiceStats::class,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin dịch vụ')
                    ->description('Thông tin chi tiết về dịch vụ được thực hiện')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Forms\Components\Select::make('service_id')
                            ->relationship('service', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Tên dịch vụ')
                            ->placeholder('Chọn dịch vụ')
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                if ($state) {
                                    $unit = \App\Models\Service::find($state)?->unit;
                                    $set('unit', $unit);
                                }
                            })
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->label('Số lượng')
                            ->placeholder('Nhập số lượng')
                            ->suffix(fn ($get) => $get('unit')),

                        Forms\Components\TextInput::make('money_received')
                            ->maxLength(15)
                            ->numeric()
                            ->label('Số tiền')
                            ->placeholder('Nhập số tiền')
                            ->prefix('VND')
                            ->numeric(),
                    ])->columns(2),

                Forms\Components\Section::make('Trạng thái')
                    ->description('Cập nhật trạng thái của dịch vụ')
                    ->icon('heroicon-o-flag')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'pending' => 'Chờ xử lý',
                                'in_progress' => 'Đang thực hiện',
                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy',
                            ])
                            ->required()
                            ->default('pending')
                            ->native(false)
                    ])->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        $taskId = request()->route('task_id');
        if (!$taskId || !Task::find($taskId)) {
            abort(404);
        }

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Tên dịch vụ')
                    ->searchable()
                    ->sortable()
                    ->description(fn (TaskService $record): string =>
                        "Số lượng: {$record->quantity} " . ($record->service->unit ?? '')),

                Tables\Columns\TextColumn::make('money_received')
                    ->label('Số tiền')
                    ->money('VND')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Chờ xử lý',
                        'in_progress' => 'Đang thực hiện',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->url(fn (TaskService $record): string =>
                        static::getUrl('edit', [
                            'record' => $record,
                            'task_id' => $taskId
                        ])
                        ),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->modifyQueryUsing(fn($query) => $query->where('task_id', $taskId));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'list' => Pages\ListTaskServices::route('/{task_id}'),
            'index' => Pages\ListTaskServices::route('/'),
            'create' => Pages\CreateTaskService::route('/create/{task_id}'),
            'edit' => Pages\EditTaskService::route('/{record}/edit'),
        ];
    }


}
