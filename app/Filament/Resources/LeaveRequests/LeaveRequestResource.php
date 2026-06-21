<?php

namespace App\Filament\Resources\LeaveRequests;

use App\Filament\Resources\LeaveRequests\Pages\CreateLeaveRequest;
use App\Filament\Resources\LeaveRequests\Pages\EditLeaveRequest;
use App\Filament\Resources\LeaveRequests\Pages\ListLeaveRequests;
use App\Filament\Resources\LeaveRequests\Schemas\LeaveRequestForm;
use App\Filament\Resources\LeaveRequests\Tables\LeaveRequestsTable;
use App\Models\LeaveRequest;
use App\Models\User;
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

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && $user->hasAnyRole([
            'super_admin', 'director', 'hr_manager', 'finance_manager', 'hrd_it',
        ]);
    }

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

    /**
     * Scope the query based on user role.
     * - Super Admin / Director / HR Manager / HRD IT / Finance Manager: see all
     * - Manager / Supervisor: scoped to their branch
     * - Others (employee / general_staff / operator): only their own records
     */
    protected static function applyUserScope(Builder $query): Builder
    {
        $user = Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Global access roles: super_admin, director, hr_manager, hrd_it, finance_manager
        if (static::hasAnyRole($user, ['super_admin', 'director', 'hr_manager', 'hrd_it', 'finance_manager'])) {
            return $query;
        }

        // Personal access only (employee)
        return $query->where('user_id', $user->id);
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

    private static function hasAnyRole($user, array $roleNames): bool
    {
        if (!$user) {
            return false;
        }

        $ownedRoles = $user->roles
            ->pluck('name')
            ->map(static fn(string $name): string => strtolower($name))
            ->all();

        $expectedRoles = array_map(static fn(string $name): string => strtolower($name), $roleNames);

        return count(array_intersect($ownedRoles, $expectedRoles)) > 0;
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

        if (static::hasAnyRole($user, ['super_admin', 'hr_manager', 'hrd_it'])) {
            return true;
        }

        return false;
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();

        if(!$user) {
            return false;
        }

        if (static::hasAnyRole($user, ['super_admin', 'hr_manager'])) {
            return true;
        }

        return false;
    }

}
