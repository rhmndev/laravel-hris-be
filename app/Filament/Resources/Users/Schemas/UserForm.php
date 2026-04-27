<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;


class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                    DateTimePicker::make('email_verified_at'),
                    TextInput::make('password')
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->password(),
                    TextInput::make('phone')
                    ->tel(),
                    FileUpload::make('profile_photo')
                        ->label('Profile Photo')
                        ->image()
                        ->disk('public')
                        ->directory('profile-photos')
                        ->visibility('public'),
                TextInput::make('employee_id'),
                Select::make('role')
                    ->options(['admin' => 'Admin', 'employee' => 'Employee'])
                    ->default('employee')
                    ->required(),
            ]);
    }
}
