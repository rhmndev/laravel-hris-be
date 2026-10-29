<?php

namespace App\Filament\Resources\Branches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('address')
                    ->words(5)
                    ->tooltip(fn (Model $record): string => $record->address)
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('radius')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('work_start_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('work_end_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('min_check_in_time')
                    ->label('Min Check-In')
                    ->time()
                    ->sortable()
                    ->placeholder('Not set'),
                TextColumn::make('max_check_in_time')
                    ->label('Max Check-In')
                    ->time()
                    ->sortable()
                    ->placeholder('Not set'),
                TextColumn::make('min_check_out_time')
                    ->label('Min Check-Out')
                    ->time()
                    ->sortable()
                    ->placeholder('Not set'),
                TextColumn::make('max_check_out_time')
                    ->label('Max Check-Out')
                    ->time()
                    ->sortable()
                    ->placeholder('Not set'),
                IconColumn::make('require_liveness')
                    ->boolean()
                    ->alignCenter()
                    ->label('Liveness Detection'),
                IconColumn::make('require_geolocation')
                    ->boolean()
                    ->alignCenter()
                    ->label('Geolocation'),
                IconColumn::make('require_face_recognation')
                    ->boolean()
                    ->alignCenter()
                    ->label('Face Recognition'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
