<?php

namespace App\Filament\Resources\Deductions\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class DeductionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Komponen')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'tax' => 'Pajak',
                        'social_security' => 'Asuransi Sosial',
                        'loan' => 'Cicilan Pinjaman',
                        'advance' => 'Uang Muka',
                        'other' => 'Lainnya',
                        default => $state,
                    }),

                TextColumn::make('amount')
                    ->label('Persentase %')
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->is_fixed
                            ? number_format($record->amount ?? 0, 0, ',', '.')
                            : ($record->percentage ?? 0) . '%'
                    ),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktif'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
