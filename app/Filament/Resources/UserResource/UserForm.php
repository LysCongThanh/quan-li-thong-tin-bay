<?php

namespace App\Filament\Resources\UserResource;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                self::createLeftColumn(),
                self::createRightColumn()
            ])->columns(12);
    }

    private static function createLeftColumn(): Section
    {
        return Section::make('Thông tin người dùng')
            ->schema([
                TextInput::make('name')
                    ->label('Tên')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->label('Mật khẩu')
                    ->password()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->same('password_confirmation')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->visible(fn(string $operation): bool => $operation === 'create'),

                TextInput::make('password_confirmation')
                    ->password()
                    ->label('Xác nhận mật khẩu')
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->visible(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(false),

            ])->columnSpan(8);
    }

    private static function createRightColumn(): Section
    {
        return Section::make('Vai trò')
            ->schema([
                Select::make('roles')
                    ->label('Vai trò')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable()
                    ->required()

            ])->columnSpan(4);
    }
}
