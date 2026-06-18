<?php

namespace App\Filament\Resources\Payrolls\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Model;

class HistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'history';

    protected static ?string $title = 'Riwayat Perubahan Payroll';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('version')
                    ->label('Versi')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Select::make('changed_by')
                    ->relationship('changedBy', 'name')
                    ->label('Diubah Oleh')
                    ->searchable()
                    ->preload(),

                Textarea::make('reason_for_change')
                    ->label('Alasan Perubahan')
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version')
                    ->label('Versi')
                    ->sortable(),

                TextColumn::make('changedBy.name')
                    ->label('Diubah Oleh')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reason_for_change')
                    ->label('Alasan')
                    ->words(5)
                    ->tooltip(fn (Model $record): string => $record->reason_for_change ?? '')
                    ->limit(40),

                TextColumn::make('changed_at')
                    ->label('Tanggal Perubahan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('version', 'desc')
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
