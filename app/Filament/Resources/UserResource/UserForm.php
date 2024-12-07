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
            ])->columns([
                'sm' => 1,        // 1 cột trên mobile
                'lg' => 12        // 12 cột trên desktop
            ]);
    }

    private static function createLeftColumn(): Section
    {
        return Section::make('User Information')
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->same('password_confirmation')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->visible(fn(string $operation): bool => $operation === 'create'),

                TextInput::make('password_confirmation')
                    ->password()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->visible(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(false),

            ])->columnSpan([
                'sm' => 'full',   // Full width trên mobile
                'lg' => 8         // 8 cột trên desktop
            ]);
    }

    private static function createRightColumn(): Section
    {
        return Section::make('Roles')
            ->schema([
                Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable()
                    ->required()

            ])->columnSpan([
                'sm' => 'full',   // Full width trên mobile
                'lg' => 4         // 4 cột trên desktop
            ]);
    }
}
