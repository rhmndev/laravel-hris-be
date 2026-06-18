<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payroll;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Payroll authorization policy aligned with PT DCCI spec Section 2:
 *
 * | Role             | Slip Gaji (Payroll) Access            |
 * |------------------|---------------------------------------|
 * | Super Admin      | Full CRUD + all workflow actions       |
 * | HR Manager/HRD   | Create/Read Draft + submit + check     |
 * | Finance Manager  | Full CRUD + approve + markPaid         |
 * | Director         | View Only + final approve/paid         |
 */
class PayrollPolicy
{
    use HandlesAuthorization;

    /**
     * Roles that can view the payroll module in admin panel.
     */
    private const VIEW_ROLES = [
        'super_admin', 'director', 'hr_manager', 'finance_manager',
        'manager', 'supervisor', 'hrd_it',
    ];

    /**
     * Roles with full CRUD (create, update, delete) on payroll.
     * Spec: Super Admin + Finance Manager.
     */
    private const FULL_EDIT_ROLES = [
        'super_admin', 'finance_manager',
    ];

    /**
     * Roles that can create payroll drafts.
     * Spec: Super Admin + HR Manager/HRD Staff.
     */
    private const CREATE_ROLES = [
        'super_admin', 'hr_manager', 'hrd_it',
    ];

    public function viewAny(AuthUser $user): bool
    {
        return $user->hasAnyRole(self::VIEW_ROLES);
    }

    public function view(AuthUser $user, Payroll $payroll): bool
    {
        if ($user->hasAnyRole(self::VIEW_ROLES)) {
            return true;
        }

        // general_staff / operator / employee can view their own payroll
        if ($user->hasAnyRole(['general_staff', 'operator', 'employee']) && $payroll->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Create payroll drafts — Super Admin + HR Manager/HRD Staff.
     */
    public function create(AuthUser $user): bool
    {
        return $user->hasAnyRole(self::CREATE_ROLES);
    }

    /**
     * Update — only when draft/rejected.
     * Full CRUD: Super Admin + Finance Manager.
     * Draft edit: HR Manager/HRD Staff (create/read draft scope).
     */
    public function update(AuthUser $user, Payroll $payroll): bool
    {
        if (!in_array($payroll->status, ['draft', 'rejected'])) {
            return false;
        }

        return $user->hasAnyRole(array_merge(self::FULL_EDIT_ROLES, self::CREATE_ROLES));
    }

    /**
     * Delete — only drafts. Super Admin + Finance Manager.
     */
    public function delete(AuthUser $user, Payroll $payroll): bool
    {
        if ($payroll->status !== 'draft') {
            return false;
        }

        return $user->hasAnyRole(self::FULL_EDIT_ROLES);
    }

    public function deleteAny(AuthUser $user): bool
    {
        return $user->hasAnyRole(self::FULL_EDIT_ROLES);
    }

    /**
     * Finance approval stage — Finance Manager + Super Admin.
     * Spec Section 3.2 Stage 1: calculated → approved_finance.
     */
    public function approve(AuthUser $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'finance_manager', 'director']);
    }

    /**
     * HR check/verification stage — HR Manager + Super Admin.
     * Spec Section 3.2 Stage 1 (HR side).
     */
    public function check(AuthUser $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'hr_manager']);
    }

    /**
     * Submit for approval — HR Manager + HRD IT + Super Admin.
     */
    public function submitForApproval(AuthUser $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'hr_manager', 'hrd_it']);
    }

    /**
     * Mark as paid — Director (final authorization) + Finance Manager + Super Admin.
     * Spec: Director View Only (Final Approve/Paid).
     */
    public function markPaid(AuthUser $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'finance_manager', 'director']);
    }
}
