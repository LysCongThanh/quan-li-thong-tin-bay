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
use Illuminate\Support\Facades\DB;

class TaskServiceResource extends Resource
{
    protected static ?string $model = TaskService::class;

    protected static ?string $pluralModelLabel = 'Báo cáo công việc';

    protected static ?string $modelLabel = 'Báo cáo công việc';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static array $tableColumns = [];
    public static $tableQuery;

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
                            ->columnSpan(['sm' => 'full', 'lg' => 2]),

                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->label('Số lượng')
                            ->placeholder('Nhập số lượng')
                            ->suffix(fn ($get) => $get('unit'))
                            ->columnSpan(['sm' => 'full', 'lg' => 1]),

                        Forms\Components\TextInput::make('money_received')
                            ->numeric()
                            ->label('Số tiền nhận')
                            ->placeholder('Nhập số tiền nhận...')
                            ->prefix('VND')
                            ->numeric()
                            ->columnSpan(['sm' => 'full', 'lg' => 1]),
                    ])->columns([
                        'sm' => 1,
                        'lg' => 2
                    ]),

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
                    ])->columnSpan(['sm' => 'full', 'lg' => '1']),

                Forms\Components\Section::make('Thông tin báo cáo')
                    ->description('Thông tin về người báo cáo và ghi chú')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\Hidden::make('reported_by')
                            ->default(auth()->id()),

                        Forms\Components\Textarea::make('note')
                            ->label('Ghi chú')
                            ->placeholder('Nhập ghi chú về công việc (nếu có)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(['sm' => 'full', 'lg' => '1']),
            ])->columns([
                'sm' => 1,  // 1 cột trên mobile
                'lg' => 2   // 1 cột trên desktop
            ]);
    }

    public static function table(Table $table): Table
    {
        $taskId = request()->route('task_id');

        if ((!$taskId || !Task::find($taskId))) {
            abort(404);
        }

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service_name')
                    ->label('Tên dịch vụ')
                    ->description(fn ($record) =>
                        "Số lượng: {$record->quantity} " . ($record->service_unit ?? '') . ", " .
                        "Đơn giá: " . number_format($record->service_price ?? 0, 0, ',', '.') . " VNĐ"
                    ),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Tổng tiền')
                    ->money('VND')
                    ->getStateUsing(fn ($record) =>
                        ($record->quantity ?? 0) * ($record->service_price ?? 0)
                    ),
                Tables\Columns\TextColumn::make('money_received')
                    ->label('Số tiền nhận')
                    ->money('VND'),
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
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Người tạo báo cáo'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
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
            'history' => Pages\ListTaskServices::route('/history/{customer_id}/'),
            'index' => Pages\ListTaskServices::route('/'),
            'create' => Pages\CreateTaskService::route('/create/{task_id}'),
            'edit' => Pages\EditTaskService::route('/{record}/edit'),
        ];
    }


}
