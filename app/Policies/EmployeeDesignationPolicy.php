<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EmployeeDesignation;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeDesignationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EmployeeDesignation');
    }

    public function view(AuthUser $authUser, EmployeeDesignation $employeeDesignation): bool
    {
        return $authUser->can('View:EmployeeDesignation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EmployeeDesignation');
    }

    public function update(AuthUser $authUser, EmployeeDesignation $employeeDesignation): bool
    {
        return $authUser->can('Update:EmployeeDesignation');
    }

    public function delete(AuthUser $authUser, EmployeeDesignation $employeeDesignation): bool
    {
        return $authUser->can('Delete:EmployeeDesignation');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EmployeeDesignation');
    }

    public function restore(AuthUser $authUser, EmployeeDesignation $employeeDesignation): bool
    {
        return $authUser->can('Restore:EmployeeDesignation');
    }

    public function forceDelete(AuthUser $authUser, EmployeeDesignation $employeeDesignation): bool
    {
        return $authUser->can('ForceDelete:EmployeeDesignation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EmployeeDesignation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EmployeeDesignation');
    }

    public function replicate(AuthUser $authUser, EmployeeDesignation $employeeDesignation): bool
    {
        return $authUser->can('Replicate:EmployeeDesignation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EmployeeDesignation');
    }

}