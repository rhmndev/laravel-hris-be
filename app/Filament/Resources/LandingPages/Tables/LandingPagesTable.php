<?php

namespace App\Filament\Resources\LandingPages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LandingPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($state) => $state),

                TextColumn::make('label')
                    ->label('Label')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('group')
                    ->label('Group')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hero' => 'primary',
                        'features' => 'success',
                        'screenshots' => 'warning',
                        'about' => 'info',
                        'footer' => 'gray',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'text' => 'gray',
                        'textarea' => 'info',
                        'image' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group', 'asc')
            ->defaultSort('sort_order', 'asc');
    }
}
