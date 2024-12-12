<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Khách Hàng';
    protected static ?string $modelLabel = 'khách hàng  ';
    protected static ?string $pluralModelLabel = 'Khách Hàng';
    protected static ?string $navigationGroup = 'Quản lí';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin khách hàng')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên Khách Hàng')
                            ->placeholder('Nhập tên khách hàng...')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('Nhập email...')
                            ->email()
                            ->required(),
                        TextInput::make('phone')
                            ->placeholder('Nhập số điện thoại...')
                            ->label('Số Điện Thoại'),
                        Textarea::make('address')
                            ->label('Địa Chỉ')
                            ->placeholder('Nhập địa chỉ...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên Khách Hàng')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('phone')->label('Số Điện Thoại'),
                TextColumn::make('created_at')->label('Ngày Tạo')->dateTime(),
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
            RelationManagers\ServiceRelationManager::class

        ];
    }

    public static function getPages(): array
    {
        return [
            'history' => Pages\History::route('history/{customer_id}'),
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
