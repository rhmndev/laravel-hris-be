<?php

namespace App\Filament\Resources\Deductions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Schemas\Schema;

class DeductionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),

                TextInput::make('name')
                    ->label('Nama Komponen')
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('type')
                    ->label('Tipe Potongan')
                    ->options([
                        'tax' => 'Pajak',
                        'social_security' => 'Asuransi Sosial',
                        'loan' => 'Cicilan Pinjaman',
                        'advance' => 'Uang Muka',
                        'other' => 'Lainnya',
                    ])
                    ->required(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2),

                Toggle::make('is_fixed')
                    ->label('Jumlah Tetap')
                    ->reactive(),

                TextInput::make('amount')
                    ->label('Jumlah Tetap (Rp)')
                    ->numeric()
                    ->visible(fn ( $get) => $get('is_fixed'))
                    ->required(fn ( $get) => $get('is_fixed')),

                TextInput::make('percentage')
                    ->label('Persentase (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->visible(fn ( $get) => !$get('is_fixed'))
                    ->required(fn ( $get) => !$get('is_fixed')),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
