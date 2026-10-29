<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('address'),
                TextInput::make('latitude')
                    ->required()
                    ->numeric(),
                TextInput::make('longitude')
                    ->required()
                    ->numeric(),
                TextInput::make('radius')
                    ->required()
                    ->numeric()
                    ->default(100),

                TimePicker::make('work_start_time')
                    ->label('Work Start Time')
                    ->required(),
                TimePicker::make('work_end_time')
                    ->label('Work End Time')
                    ->required(),

                // Check-In Time Window
                TimePicker::make('min_check_in_time')
                    ->label('Min Check-In Time')
                    ->nullable()
                    ->helperText('Earliest time an employee can check in (e.g. 06:00). Leave empty to allow any time.'),
                TimePicker::make('max_check_in_time')
                    ->label('Max Check-In Time')
                    ->nullable()
                    ->helperText('Latest time an employee can check in (e.g. 09:00). Leave empty to allow any time.'),

                // Check-Out Time Window
                TimePicker::make('min_check_out_time')
                    ->label('Min Check-Out Time')
                    ->nullable()
                    ->helperText('Earliest time an employee can check out (e.g. 16:15). Leave empty to allow any time.'),
                TimePicker::make('max_check_out_time')
                    ->label('Max Check-Out Time')
                    ->nullable()
                    ->helperText('Latest time an employee can check out (e.g. 18:00). Leave empty to allow any time.'),

                Toggle::make('require_liveness')
                    ->label('Liveness Detection')
                    ->default(true)
                    ->inline(false),
                Toggle::make('require_geolocation')
                    ->label('Geolocation')
                    ->default(true)
                    ->inline(false),
                Toggle::make('require_face_recognation')
                    ->label('Face Recognition')
                    ->default(true)
                    ->inline(false),

            ]);
    }
}
