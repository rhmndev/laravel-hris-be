<?php

namespace App\Filament\Resources\LeaveRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class LeaveRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->required(),
                Select::make('branch_id')
                    ->disabled()
                    ->relationship('branch', 'name')
                    ->required(),
                Select::make('type')
                    ->disabled()
                    ->options(['cuti_tahunan' => 'Cuti tahunan', 'cuti_sakit' => 'Cuti sakit'])
                    ->required(),
                DatePicker::make('start_date')
                    ->disabled()
                    ->required(),
                DatePicker::make('end_date')
                    ->disabled()
                    ->required(),
                TextInput::make('total_days')
                    ->label('Total Hari')
                    ->disabled()
                    ->required()
                    ->numeric(),
                Textarea::make('reason')
                    ->label('Alasan')
                    ->required()
                    ->disabled()
                    ->columnSpanFull(),
                FileUpload::make('attachment')
                    ->label('Lampiran (Jika Ada)')
                    ->disk('public')
                    ->directory('leave-attachments')
                    ->visibility('public')
                    ->dehydrated(false)
                    ->ColumnSpanFull()
                    ->disabled(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->required()
                    // ->disabled(fn (): bool => !static::canReview()),
                    ->disabled(),
                TextInput::make('reviewed_by')
                    ->disabled(),
                // DateTimePicker::make('reviewed_at')
                //     ->required(),
                Textarea::make('review_notes')
                    ->columnSpanFull(),
            ]);
    }
}
