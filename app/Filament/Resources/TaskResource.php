<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Customer;
use App\Models\Task;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $pluralModelLabel = 'Công việc';

    protected static ?string $modelLabel = 'công việc';

    protected static ?string $navigationLabel = 'Công việc';

    protected static ?string $navigationGroup = 'Quản lí';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin công việc')
                    ->description('Nhập thông tin chi tiết về công việc cần thực hiện')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên công việc')
                            ->required()
                            ->placeholder('Nhập tên công việc')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Mô tả')
                            ->required()
                            ->placeholder('Mô tả chi tiết công việc')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columnSpan(1),

                Forms\Components\Section::make('Phân công nhân sự')
                    ->description('Chọn nhân sự thực hiện công việc')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Forms\Components\Select::make('pilot_id')
                            ->label('Phi công chính')
                            ->relationship(
                                'pilot',
                                'name',
                                fn ($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'Nhân viên'))
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Chọn phi công')
                            ->live()
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'edit' || $operation === 'create') {
                                    $set('support_id', null);
                                }
                            }),

                        Forms\Components\Select::make('support_id')
                            ->label('Nhân viên hỗ trợ')
                            ->relationship(
                                'support',
                                'name',
                                function ($query, Forms\Get $get) {
                                    $query->whereHas('roles', fn($q) => $q->where('name', 'Nhân viên'))
                                        ->when(
                                            $get('pilot_id'),
                                            fn ($q) => $q->where('id', '!=', $get('pilot_id'))
                                        );
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Chọn nhân viên hỗ trợ')
                            ->disabled(fn (Forms\Get $get) => ! $get('pilot_id')),
                    ])->columnSpan(1),

                Forms\Components\Section::make('Thông tin khách hàng')
                    ->description('Thông tin về khách hàng yêu cầu công việc')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Select::make('customer_name')
                            ->label('Tên khách hàng')
                            ->options(Customer::all()->pluck('name', 'name'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Chọn tên khách hàng')
                            ->live()
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'edit' || $operation === 'create') {
                                    $set('customer_id', null);
                                }
                            })
                    ])->columnSpan(1),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên công việc')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn(Task $record): string => $record->description)
                    ->width(300),

                TextColumn::make('customer_name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('pilot.name')
                    ->label('Phi công chính')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-circle'),

                TextColumn::make('support.name')
                    ->label('Hỗ trợ')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-group'),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('pilot')
                    ->relationship('pilot', 'name')
                    ->label('Lọc theo phi công'),
                Tables\Filters\SelectFilter::make('support')
                    ->relationship('support', 'name')
                    ->label('Lọc theo nhân viên hỗ trợ'),
            ])
            ->filtersFormColumns(2)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalWidth('5xl')
                        ->modalHeading('Chi tiết công việc'),
                    Tables\Actions\EditAction::make()
                        ->visible(fn() => auth()->user()->hasRole(['super_admin', 'owner'])),
                    Tables\Actions\Action::make('report')
                        ->label('Báo cáo công việc')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->url(fn(Task $record): string => TaskServiceResource::getUrl('list', ['task_id' => $record->id]))
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
//            RelationManagers\TaskServiceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
