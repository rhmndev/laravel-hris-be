<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\ImageColumn;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->sortable()
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                ImageColumn::make('profile_photo')
                    ->label('Foto')
                    ->disk('public')
                    ->circular()
                    ->alignCenter()
                    ->ImageHeight(48)
                    ->ImageWidth(48)
                    ->placeholder('-'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->icon(Heroicon::User)
                    ->iconColor('primary')
                    ->iconPosition(IconPosition::After)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee_id')
                    ->label('ID Karyawan')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->icon(Heroicon::Envelope)
                    ->iconColor('primary')
                    ->iconPosition(IconPosition::After)
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('No. HP Kantor')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone_personal')
                    ->label('No. HP Personal')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'info',
                        'director' => 'primary',
                        'hr_manager' => 'warning',
                        'finance_manager' => 'warning',
                        'hrd_it' => 'info',
                        'employee' => 'success',
                        default => 'danger',
                    }),
                TextColumn::make('employee_status')
                    ->label('Status Karyawan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'kontrak' => 'warning',
                        'magang' => 'info',
                        'resign' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aktif' => 'Aktif',
                        'kontrak' => 'Kontrak',
                        'magang' => 'Magang',
                        'resign' => 'Resign',
                        default => $state ?: '-',
                    })
                    ->toggleable(),
                TextColumn::make('join_date')
                    ->label('Tanggal Bergabung')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('date_of_birth')
                    ->label('Tanggal Lahir')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('identity_number')
                    ->label('No. KTP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bank_name')
                    ->label('Bank')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bank_account_number')
                    ->label('No. Rekening')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->address)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
