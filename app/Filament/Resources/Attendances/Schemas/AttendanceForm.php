<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                DatePicker::make('attendance_date')
                    ->required(),
                DateTimePicker::make('check_in_time')
                    ->required(),
                TextInput::make('check_in_latitude')
                    ->required()
                    ->numeric(),
                TextInput::make('check_in_longitude')
                    ->required()
                    ->numeric(),
                FileUpload::make('check_in_photo')
                    ->disk('public')
                    ->required(),
                DateTimePicker::make('check_out_time'),
                TextInput::make('check_out_latitude')
                    ->numeric(),
                TextInput::make('check_out_longitude')
                    ->numeric(),
                FileUpload::make('check_out_photo')
                    ->required()
                    ->disk('public'),
                Select::make('status')
                    ->options(['on_time' => 'On time', 'late' => 'Late'])
                    ->default('on_time')
                    ->required(),
            ]);
    }
}
