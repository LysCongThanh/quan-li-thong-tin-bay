<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Customer;
use App\Models\Task;
use Carbon\Carbon;
use Filament\Forms\Components\TextInput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;
use Illuminate\Contracts\Database\Eloquent\Builder;

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
                            ->rows(4)
                            ->required()
                            ->placeholder('Mô tả chi tiết công việc')
                            ->columnSpanFull(),
                    ])->columnSpan(['lg' => 1, 'sm' => 'full']),

                Forms\Components\Section::make('Phân công nhân sự')
                    ->description('Chọn nhân sự thực hiện công việc')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Forms\Components\Select::make('pilot_id')
                            ->label('Phi công chính')
                            ->relationship(
                                'pilot',
                                'name',
                                fn($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'Nhân viên'))
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
                                            fn($q) => $q->where('id', '!=', $get('pilot_id'))
                                        );
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Chọn nhân viên hỗ trợ')
                            ->disabled(fn(Forms\Get $get) => !$get('pilot_id')),
                    ])->columnSpan(['lg' => 1, 'sm' => 'full']),

                Forms\Components\Section::make('Thông tin khách hàng')
                    ->description('Thông tin về khách hàng yêu cầu công việc')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Tên khách hàng')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->placeholder('Chọn tên khách hàng')
                            ->live()
                            ->afterStateHydrated(function ($state, Forms\Set $set) {
                                // Fill data khi load form edit
                                if ($state) {
                                    $customer = Customer::find($state);
                                    $set('customer_email', $customer->email);
                                    $set('customer_phone', $customer->phone);
                                    $set('customer_address', $customer->address);
                                }
                            })
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Update khi chọn customer mới
                                if ($state) {
                                    $customer = Customer::find($state);
                                    $set('customer_email', $customer->email);
                                    $set('customer_phone', $customer->phone);
                                    $set('customer_address', $customer->address);
                                } else {
                                    $set('customer_email', null);
                                    $set('customer_phone', null);
                                    $set('customer_address', null);
                                }
                            })
                            ->createOptionForm([
                                Forms\Components\Section::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Tên khách hàng')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Nhập tên khách hàng')
                                            ->columnSpan(1),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->unique('customers', 'email')
                                            ->nullable()
                                            ->placeholder('example@domain.com')
                                            ->columnSpan(1),

                                        TextInput::make('phone')
                                            ->label('Số điện thoại')
                                            ->tel()
                                            ->nullable()
                                            ->placeholder('0123456789')
                                            ->columnSpan(1),

                                        Textarea::make('address')
                                            ->label('Địa chỉ')
                                            ->nullable()
                                            ->placeholder('Nhập địa chỉ')
                                            ->rows(4)
                                            ->columnSpan('full'),
                                    ])
                                    ->columns(3)
                            ])
                            ->createOptionUsing(function (array $data) {
                                $customer = Customer::create($data);
                                return $customer->id;
                            })
                            ->createOptionModalHeading('Thêm khách hàng mới'),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('customer_email')
                                    ->label('Email')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('customer_phone')
                                    ->label('Số điện thoại')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\Textarea::make('customer_address')
                                    ->label('Địa chỉ')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->visible(fn(Forms\Get $get) => filled($get('customer_id')))
                    ])->columnSpan(2),
            ])->columns([
                'sm' => 1, // 1 cột trên mobile
                'lg' => 2  // 2 cột trên desktop
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (auth()->user()->hasRole(['super_admin', 'owner'])) {
                    return $query;
                }

                return $query->where(function ($q) {
                    $q->where('pilot_id', auth()->id())
                        ->orWhere('support_id', auth()->id());
                });
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Tên công việc')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('customer.name')
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
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('created_from')
                                    ->label('Từ ngày'),
                                Forms\Components\DatePicker::make('created_until')
                                    ->label('Đến ngày'),
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Từ ngày ' . Carbon::parse($data['created_from'])->format('d/m/Y'))
                                ->removeField('created_from');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Đến ngày ' . Carbon::parse($data['created_until'])->format('d/m/Y'))
                                ->removeField('created_until');
                        }

                        return $indicators;
                    })->columnSpanFull()
            ], layout: Tables\Enums\FiltersLayout::Dropdown)
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn() => !auth()->user()->hasRole(['super_admin', 'owner'])),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()->hasRole(['super_admin', 'owner'])),
                Tables\Actions\Action::make('report')
                    ->label('Báo cáo công việc')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn(Task $record): string => TaskServiceResource::getUrl('list', ['task_id' => $record->id]))
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
