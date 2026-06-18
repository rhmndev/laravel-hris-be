<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Earning;
use Illuminate\Auth\Access\HandlesAuthorization;

class EarningPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Earning');
    }

    public function view(AuthUser $authUser, Earning $earning): bool
    {
        return $authUser->can('View:Earning');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Earning');
    }

    public function update(AuthUser $authUser, Earning $earning): bool
    {
        return $authUser->can('Update:Earning');
    }

    public function delete(AuthUser $authUser, Earning $earning): bool
    {
        return $authUser->can('Delete:Earning');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Earning');
    }

    public function restore(AuthUser $authUser, Earning $earning): bool
    {
        return $authUser->can('Restore:Earning');
    }

    public function forceDelete(AuthUser $authUser, Earning $earning): bool
    {
        return $authUser->can('ForceDelete:Earning');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Earning');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Earning');
    }

    public function replicate(AuthUser $authUser, Earning $earning): bool
    {
        return $authUser->can('Replicate:Earning');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Earning');
    }

}