<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payroll;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayrollPolicy
{
    use HandlesAuthorization;

    /**
     * Roles that can access the admin panel payroll module.
     */
    private const ADMIN_ROLES = [
        'super_admin', 'director', 'hr_manager', 'finance_manager',
        'manager', 'supervisor', 'hrd_it', 'operator',
    ];

    private const EDIT_ROLES = [
        'super_admin', 'director', 'hr_manager', 'operator',
    ];

    public function viewAny(AuthUser $user): bool
    {
        return $user->hasAnyRole(self::ADMIN_ROLES);
    }

    public function view(AuthUser $user, Payroll $payroll): bool
    {
        if ($user->hasAnyRole(self::ADMIN_ROLES)) {
            return true;
        }

        // general_staff can view their own payroll
        if ($user->hasRole('general_staff') && $payroll->user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function create(AuthUser $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'director', 'hr_manager', 'operator', 'hrd_it']);
    }

    public function update(AuthUser $user, Payroll $payroll): bool
    {
        // Only allow editing when status is draft or rejected
        if (!in_array($payroll->status, ['draft', 'rejected'])) {
            return false;
        }

        return $user->hasAnyRole(self::EDIT_ROLES);
    }

    public function delete(AuthUser $user, Payroll $payroll): bool
    {
        if ($payroll->status !== 'draft') {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'director']);
    }

    public function deleteAny(AuthUser $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'director']);
    }

    public function approve(AuthUser $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'finance_manager', 'director']);
    }

    public function check(AuthUser $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'hr_manager']);
    }

    public function submitForApproval(AuthUser $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'hr_manager', 'hrd_it']);
    }

    public function markPaid(AuthUser $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'finance_manager', 'hr_manager']);
    }
}
