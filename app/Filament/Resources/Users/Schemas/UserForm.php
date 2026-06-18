<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->components([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Terverifikasi'),
                        TextInput::make('password')
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->password(),
                        Select::make('roles')
                            ->label('Roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->minItems(1)
                            ->maxItems(1)
                            ->required()
                            ->preload(),
                    ]),

                Section::make('Informasi Karyawan')
                    ->components([
                        Grid::make(2)->components([
                            TextInput::make('employee_id')
                                ->label('ID Karyawan'),
                            Select::make('branch_id')
                                ->relationship('branch', 'name')
                                ->label('Branch')
                                ->searchable()
                                ->preload(),
                        ]),
                        Grid::make(2)->components([
                            TextInput::make('phone')
                                ->label('No. HP Kantor')
                                ->tel(),
                            TextInput::make('phone_personal')
                                ->label('No. HP Personal')
                                ->tel(),
                        ]),
                        Grid::make(2)->components([
                            DatePicker::make('join_date')
                                ->label('Tanggal Bergabung'),
                            Select::make('employee_status')
                                ->label('Status Karyawan')
                                ->options([
                                    'aktif' => 'Aktif',
                                    'kontrak' => 'Kontrak',
                                    'magang' => 'Magang',
                                    'resign' => 'Resign',
                                ]),
                        ]),
                        FileUpload::make('profile_photo')
                            ->label('Foto Profil')
                            ->image()
                            ->disk('public')
                            ->directory('profile-photos')
                            ->visibility('public'),
                    ]),

                Section::make('Data Diri')
                    ->components([
                        Grid::make(2)->components([
                            TextInput::make('identity_number')
                                ->label('No. KTP'),
                            DatePicker::make('date_of_birth')
                                ->label('Tanggal Lahir'),
                        ]),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(2),
                    ]),

                Section::make('Informasi Bank')
                    ->components([
                        Grid::make(2)->components([
                            TextInput::make('bank_name')
                                ->label('Nama Bank')
                                ->placeholder('Contoh: BCA, Mandiri, BNI'),
                            TextInput::make('bank_account_number')
                                ->label('No. Rekening'),
                        ]),
                    ]),
            ]);
    }
}
