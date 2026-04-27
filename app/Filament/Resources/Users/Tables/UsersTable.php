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
                    ->label('Profile Photo')
                    ->disk('public')
                    ->circular()
                    ->alignCenter()
                    ->ImageHeight(60)
                    ->ImageWidth(60)
                    ->placeholder('-')
                    ->visibility('public'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->icon(Heroicon::User)
                    ->iconColor('primary')
                    ->iconPosition(IconPosition::After)
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email Address')
                    ->icon(Heroicon::Envelope)
                    ->iconColor('primary')
                    ->iconPosition(IconPosition::After)
                    ->searchable(),
                TextColumn::make('employee_id')
                    ->label('ID Karyawan')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'info',
                        'employee' => 'success',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
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
