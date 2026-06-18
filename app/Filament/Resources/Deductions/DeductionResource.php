<?php

namespace App\Filament\Resources\Deductions;

use App\Filament\Resources\Deductions\Pages\CreateDeduction as CreateDeduction;
use App\Filament\Resources\Deductions\Pages\EditDeduction as EditDeduction;
use App\Filament\Resources\Deductions\Pages\ListDeductions as ListDeductions;
use App\Filament\Resources\Deductions\Schemas\DeductionForm;
use App\Filament\Resources\Deductions\Tables\DeductionsTable;
use App\Models\Deduction;
use BackedEnum;
use UnitEnum;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeductionResource extends Resource
{
    protected static ?string $model = Deduction::class;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'director', 'hr_manager', 'finance_manager', 'hrd_it']);
    }

    // Menggunakan Heroicon enum sesuai arsitektur project Anda
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingDown;

    protected static ?string $navigationLabel = 'Komponen Potongan';
    protected static string|UnitEnum|null $navigationGroup = 'Payroll Master';
    protected static ?int $navigationSort = 3;

    // Menggunakan Schema kelas kustom, bukan Form standar
    public static function form(Schema $schema): Schema
    {
        return DeductionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeductionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeductions::route('/'),
            'create' => CreateDeduction::route('/create'),
            'edit' => EditDeduction::route('/{record}/edit'),
        ];
    }
}
