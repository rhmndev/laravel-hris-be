<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('attendance_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_in_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('check_in_latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_in_longitude')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('check_in_photo')
                    ->disk('public')
                    ->label('Check-in Photo')
                    ->imageHeight(60)
                    ->imageWidth(60),
                TextColumn::make('check_out_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('check_out_latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_out_longitude')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('check_out_photo')
                    ->disk('public')
                    ->label('Check-out Photo')
                    ->imageHeight(70)
                    ->imageWidth(70),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'on_time' => 'success',
                        'late' => 'danger',
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
