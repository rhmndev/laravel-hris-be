<?php

namespace App\Filament\Resources\Shifts;

use App\Filament\Resources\Shifts\Pages\CreateShift;
use App\Filament\Resources\Shifts\Pages\EditShift;
use App\Filament\Resources\Shifts\Pages\ListShifts;
use App\Models\Shift;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use UnitEnum;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'director', 'hr_manager', 'hrd_it']);
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Shift Kerja';
    protected static string|UnitEnum|null $navigationGroup = 'Payroll Master';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),

                TextInput::make('name')
                    ->label('Nama Shift')
                    ->required()
                    ->maxLength(100),

                TimePicker::make('clock_in_time')
                    ->label('Jam Masuk')
                    ->required()
                    ->seconds(false),

                TimePicker::make('clock_out_time')
                    ->label('Jam Pulang')
                    ->required()
                    ->seconds(false),

                TextInput::make('late_tolerance_minutes')
                    ->label('Toleransi Terlambat (menit)')
                    ->numeric()
                    ->default(15),

                TimePicker::make('early_clock_in_time')
                    ->label('Jam Masuk Paling Awal')
                    ->seconds(false)
                    ->helperText('Karyawan bisa clock-in sebelum jam masuk'),

                TimePicker::make('allow_clock_out_till')
                    ->label('Jam Pulang Paling Lama')
                    ->seconds(false)
                    ->helperText('Batas waktu clock-out terbaru'),

                Toggle::make('self_clocking')
                    ->label('Karyawan Clock In/Out Sendiri')
                    ->default(true),
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

                TextColumn::make('name')
                    ->label('Nama Shift')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('clock_in_time')
                    ->label('Jam Masuk')
                    ->sortable(),

                TextColumn::make('clock_out_time')
                    ->label('Jam Pulang')
                    ->sortable(),

                TextColumn::make('late_tolerance_minutes')
                    ->label('Toleransi (menit)')
                    ->sortable(),

                IconColumn::make('self_clocking')
                    ->label('Self Clocking')
                    ->boolean(),

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
            'index' => ListShifts::route('/'),
            'create' => CreateShift::route('/create'),
            'edit' => EditShift::route('/{record}/edit'),
        ];
    }
}
