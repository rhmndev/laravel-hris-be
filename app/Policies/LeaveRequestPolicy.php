<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LeaveRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LeaveRequest');
    }

    public function view(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        return $authUser->can('View:LeaveRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LeaveRequest');
    }

    public function update(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        return $authUser->can('Update:LeaveRequest');
    }

    public function delete(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        return $authUser->can('Delete:LeaveRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LeaveRequest');
    }

    public function restore(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        return $authUser->can('Restore:LeaveRequest');
    }

    public function forceDelete(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        return $authUser->can('ForceDelete:LeaveRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LeaveRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LeaveRequest');
    }

    public function replicate(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        return $authUser->can('Replicate:LeaveRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LeaveRequest');
    }

    /**
     * Determine who can approve leave requests in the Filament admin panel.
     *
     * - Super Admin: always allowed
     * - HR Manager: can approve leave from Operator / General Staff / Supervisor level employees
     * - Director: can approve all leave requests across all levels (final authorization)
     */
    public function approve(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        /** @var User $authUser */
        // Super admin otomatis mendapatkan izin
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        // HRD berhak menyetujui cuti dari karyawan level Employee
        if ($authUser->hasAnyRole('hr_manager', 'hrd_it')) {
            $requester = $leaveRequest->user;
            if ($requester && $requester->hasAnyRole(['employee'])) {
                return true;
            }
        }

        // Director berhak menyetujui seluruh pengajuan cuti di semua level jabatan
        if ($authUser->hasRole('director')) {
            return true;
        }

        return false;
    }

    /**
     * Determine who can reject leave requests.
     * Same role requirements as approve.
     */
    public function reject(AuthUser $authUser, LeaveRequest $leaveRequest): bool
    {
        return $this->approve($authUser, $leaveRequest);
    }
}
