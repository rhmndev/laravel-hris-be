<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TaxSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaxSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TaxSetting');
    }

    public function view(AuthUser $authUser, TaxSetting $taxSetting): bool
    {
        return $authUser->can('View:TaxSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TaxSetting');
    }

    public function update(AuthUser $authUser, TaxSetting $taxSetting): bool
    {
        return $authUser->can('Update:TaxSetting');
    }

    public function delete(AuthUser $authUser, TaxSetting $taxSetting): bool
    {
        return $authUser->can('Delete:TaxSetting');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TaxSetting');
    }

    public function restore(AuthUser $authUser, TaxSetting $taxSetting): bool
    {
        return $authUser->can('Restore:TaxSetting');
    }

    public function forceDelete(AuthUser $authUser, TaxSetting $taxSetting): bool
    {
        return $authUser->can('ForceDelete:TaxSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TaxSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TaxSetting');
    }

    public function replicate(AuthUser $authUser, TaxSetting $taxSetting): bool
    {
        return $authUser->can('Replicate:TaxSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TaxSetting');
    }

}