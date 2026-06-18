<?php

namespace App\Filament\Resources\Payrolls\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    protected static ?string $title = 'Detail Komponen Gaji';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('component_type')
                    ->label('Tipe Komponen')
                    ->options([
                        'earning' => 'Penghasilan',
                        'deduction' => 'Potongan',
                    ])
                    ->required(),

                TextInput::make('component_name')
                    ->label('Nama Komponen')
                    ->required()
                    ->maxLength(100),

                TextInput::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('component_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'earning' => 'Penghasilan',
                        'deduction' => 'Potongan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'earning' => 'success',
                        'deduction' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('component_name')
                    ->label('Nama Komponen')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make(),
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
