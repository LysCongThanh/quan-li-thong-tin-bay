<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;


class ServiceResource extends Resource
{
    protected static ?string $navigationLabel = 'Dịch Vụ';

    protected static ?string $pluralModelLabel = 'Dịch Vụ';

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationGroup = 'Quản lí';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Tạo mới dịch vụ')
                    ->description('Mỗi dịch vụ đại diện cho một hoạt động nông nghiệp cụ thể như xịt thuốc, xạ phân, cày xới đất,... Thông tin này sẽ được sử dụng trong các báo cáo công việc.')
                    ->icon('heroicon-o-rocket-launch')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Tên dịch vụ')
                                    ->placeholder('Nhập tên dịch vụ...')
                                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Ví dụ: Phun thuốc')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('unit')
                                    ->label('Đơn vị')
                                    ->placeholder('Nhập đơn vị...')
                                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Đơn vị: Bình, bao,..')
                                    ->required()
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên dịch vụ')
                    ->searchable(),
                TextColumn::make('unit')
                    ->label('Đơn vị')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Ngày thêm')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
