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
                    ->required(),
                TimePicker::make('work_end_time')
                    ->required(),
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
