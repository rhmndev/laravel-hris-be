<?php

namespace App\Filament\Resources\EmployeeDesignations;

use App\Filament\Resources\EmployeeDesignations\Pages\CreateEmployeeDesignation;
use App\Filament\Resources\EmployeeDesignations\Pages\EditEmployeeDesignation;
use App\Filament\Resources\EmployeeDesignations\Pages\ListEmployeeDesignations;
use App\Models\EmployeeDesignation;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use UnitEnum;

class EmployeeDesignationResource extends Resource
{
    protected static ?string $model = EmployeeDesignation::class;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'director', 'hr_manager', 'hrd_it']);
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Penugasan Karyawan';
    protected static string|UnitEnum|null $navigationGroup = 'Payroll Master';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Karyawan')
                    ->relationship('user', 'name', modifyQueryUsing: function (Builder $query, $livewire) {
                        // Get the current user_id when editing (keep current employee selectable)
                        $currentUserId = $livewire->record?->user_id ?? null;

                        // Get IDs of users who already have an active designation
                        $assignedUserIds = EmployeeDesignation::query()
                            ->where('is_active', true)
                            ->when($currentUserId, fn (Builder $q) => $q->where('user_id', '!=', $currentUserId))
                            ->pluck('user_id');

                        // Only show employees who are NOT already assigned
                        $query->whereNotIn('id', $assignedUserIds);
                    })
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('designation_id')
                    ->relationship('designation', 'name')
                    ->label('Jabatan')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('shift_id')
                    ->relationship('shift', 'name')
                    ->label('Shift Kerja')
                    ->searchable()
                    ->preload(),

                TextInput::make('basic_salary')
                    ->label('Gaji Pokok')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('allowances')
                    ->label('Tunjangan')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->helperText('Total tunjangan (transport, makan, dll)'),

                DatePicker::make('effective_date')
                    ->label('Tanggal Efektif'),

                DatePicker::make('end_date')
                    ->label('Tanggal Berakhir'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('designation.name')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('shift.name')
                    ->label('Shift')
                    ->sortable()
                    ->default('-'),

                TextColumn::make('basic_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('allowances')
                    ->label('Tunjangan')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('effective_date')
                    ->label('Mulai Aktif')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Berakhir')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
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

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeDesignations::route('/'),
            'create' => CreateEmployeeDesignation::route('/create'),
            'edit' => EditEmployeeDesignation::route('/{record}/edit'),
        ];
    }
}
