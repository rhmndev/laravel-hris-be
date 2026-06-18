<?php

namespace App\Filament\Resources\TaxSettings;

use App\Filament\Resources\TaxSettings\Pages\CreateTaxSetting;
use App\Filament\Resources\TaxSettings\Pages\EditTaxSetting;
use App\Filament\Resources\TaxSettings\Pages\ListTaxSettings;
use App\Models\TaxSetting;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use UnitEnum;

class TaxSettingResource extends Resource
{
    protected static ?string $model = TaxSetting::class;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'director', 'hr_manager', 'finance_manager']);
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Pengaturan Pajak';
    protected static string|UnitEnum|null $navigationGroup = 'Payroll Master';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),

                TextInput::make('year')
                    ->label('Tahun')
                    ->numeric()
                    ->required()
                    ->default(date('Y')),

                TextInput::make('pph21_rate')
                    ->label('Tarif PPh 21 (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),

                TextInput::make('bpjs_kesehatan_rate')
                    ->label('BPJS Kesehatan Karyawan (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(4.00)
                    ->suffix('%'),

                TextInput::make('bpjs_ketenagakerjaan_rate')
                    ->label('BPJS Ketenagakerjaan (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0.24)
                    ->suffix('%'),

                TextInput::make('jht_rate')
                    ->label('JHT (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(3.97)
                    ->suffix('%'),

                TextInput::make('non_taxable_income')
                    ->label('PTKP (Penghasilan Tidak Kena Pajak)')
                    ->numeric()
                    ->prefix('Rp')
                    ->helperText('Penghasilan tidak kena pajak tahunan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),

                TextColumn::make('pph21_rate')
                    ->label('PPh 21')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('bpjs_kesehatan_rate')
                    ->label('BPJS Kesehatan')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('bpjs_ketenagakerjaan_rate')
                    ->label('BPJS TK')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('jht_rate')
                    ->label('JHT')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('non_taxable_income')
                    ->label('PTKP')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getPages(): array
    {
        return [
            'index' => ListTaxSettings::route('/'),
            'create' => CreateTaxSetting::route('/create'),
            'edit' => EditTaxSetting::route('/{record}/edit'),
        ];
    }
}
