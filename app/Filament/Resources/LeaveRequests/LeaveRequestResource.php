<?php

namespace App\Filament\Resources\LeaveRequests;

use App\Filament\Resources\LeaveRequests\Pages\CreateLeaveRequest;
use App\Filament\Resources\LeaveRequests\Pages\EditLeaveRequest;
use App\Filament\Resources\LeaveRequests\Pages\ListLeaveRequests;
use App\Filament\Resources\LeaveRequests\Schemas\LeaveRequestForm;
use App\Filament\Resources\LeaveRequests\Tables\LeaveRequestsTable;
use App\Models\LeaveRequest;
use App\Models\Users;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Override;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LeaveRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeaveRequestsTable::configure($table);
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
            'index' => ListLeaveRequests::route('/'),
            'create' => CreateLeaveRequest::route('/create'),
            'edit' => EditLeaveRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return static::applyUserScope(parent::getEloquentQuery());
    }

     protected static function applyUserScope(Builder $query): Builder
    {
        $user = Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (static::hasRole('super_admin')) {
            return $query;
        }

        if (static::hasRole('manager')) {
            return filled($user->branch_id)
                ? $query->where('branch_id', $user->branch_id)
                : $query->whereRaw('1 = 0');
        }

        if (static::hasRole('employee')) {
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1 = 0');

    }

    private static function hasRole(string $roleName): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        $ownedRoles = $user->roles
            ->pluck('name')
            ->map(static fn(string $name):string => strtolower($name))
            ->all();

            return in_array(strtolower($roleName), $ownedRoles, true);
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();

        if(!$user) {
            return false;
        }

        if (static::hasRole('super_admin')) {
            return true;
        }

        if (static::hasRole('manager')) {
            return filled($record->branch_id)
                ? $record->branch_id === $user->branch_id
                : false;
        }

        // if (static::hasRole('manager')) {
        //     return (int) $record->branch_id === $user->branch_id;
        // }

        return false;
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();

        if(!$user) {
            return false;
        }

        if (static::hasRole('super_admin')) {
            return true;
        }

        if (static::hasRole('manager')) {
            return filled($record->branch_id)
                ? $record->branch_id === $user->branch_id
                : false;
        }

        // if (static::hasRole('manager')) {
        //     return (int) $record->branch_id === $user->branch_id;
        // }

        return false;
    }


}
