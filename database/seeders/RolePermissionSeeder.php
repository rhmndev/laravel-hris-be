<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Role & Permission Seeder for PT Dharma Control Cable Indonesia (PT DCCI).
 *
 * Implements the technical specification Section 2 — Permission Matrix:
 *
 * | Module                   | Super Admin | HR Manager/HRD  | Finance Manager | Director         |
 * |--------------------------|-------------|-----------------|-----------------|------------------|
 * | Filament Shield (Roles)  | Full CRUD   | No Access       | No Access       | No Access        |
 * | User & Employee Desig.   | Full CRUD   | Full CRUD       | View Only       | View Only        |
 * | Attendance (Presensi)    | Full CRUD   | Full CRUD       | View Only       | View Only        |
 * | Shift & Branch           | Full CRUD   | Full CRUD       | No Access       | View Only        |
 * | Leave Request (Cuti)     | Full CRUD   | Full CRUD+Approve| View Only      | View Only+Approve|
 * | Payroll Master (E/D)     | Full CRUD   | View Only       | Full CRUD       | View Only        |
 * | Tax Setting              | Full CRUD   | No Access       | Full CRUD       | View Only        |
 * | Slip Gaji (Payroll)      | Full CRUD   | Create/Read Draft| Full CRUD+Approve| View Only+Paid |
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Custom Workflow Permissions ─────────────────────────────
        $customPermissions = [
            'Approve:Payroll',      // Finance/Director approval stage
            'Check:Payroll',        // HR verification stage
            'Submit:Payroll',       // HR submit for approval
            'Calculate:Payroll',    // HR/HRD calculate payroll
            'MarkPaid:Payroll',     // Mark payroll as paid (Finance/Director)
            'Approve:LeaveRequest', // Leave request approval (HR/Director)
        ];

        foreach ($customPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Resource Permissions (Filament Shield CRUD methods) ─────
        $resources = [
            'Attendance', 'Branch', 'Company', 'Deduction', 'Designation',
            'Earning', 'EmployeeDesignation', 'LeaveRequest', 'Payroll',
            'Shift', 'TaxSetting', 'User',
        ];

        $methods = [
            'ViewAny', 'View', 'Create', 'Update', 'Delete',
            'DeleteAny', 'Restore', 'ForceDelete', 'ForceDeleteAny',
            'RestoreAny', 'Replicate', 'Reorder',
        ];

        foreach ($resources as $resource) {
            foreach ($methods as $method) {
                Permission::firstOrCreate(
                    ['name' => "{$method}:{$resource}", 'guard_name' => 'web']
                );
            }
        }

        // ── Permission Helpers ──────────────────────────────────────
        // Full CRUD permissions for a resource
        $allPerms = fn(string $r) => collect($methods)->map(fn($m) => "{$m}:{$r}")->all();
        // View-only permissions for a resource
        $viewPerms = fn(string $r) => ["ViewAny:{$r}", "View:{$r}"];

        // ════════════════════════════════════════════════════════════
        //  SPEC SECTION 1.1 — Level 1: Super Admin (IT / Developer)
        //  Full CRUD on ALL modules (handled by Shield gate:before).
        // ════════════════════════════════════════════════════════════
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::where('guard_name', 'web')->get());

        // ════════════════════════════════════════════════════════════
        //  SPEC SECTION 1.2 — Level 2: Operational HR (HRGA & IT)
        //  Modules: User ✓, Attendance ✓, Shift ✓, Branch ✓,
        //           LeaveRequest ✓+Approve, Payroll Create/Read Draft,
        //           Payroll Master ✗, Tax Setting ✗
        // ════════════════════════════════════════════════════════════
        $hrManager = Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);
        $hrManagerPerms = array_merge(
            // User & Employee Designation: Full CRUD
            $allPerms('User'),
            $allPerms('EmployeeDesignation'),
            $allPerms('Designation'),
            // Attendance (Presensi): Full CRUD
            $allPerms('Attendance'),
            // Shift Kerja & Cabang: Full CRUD
            $allPerms('Shift'),
            $allPerms('Branch'),
            // Leave Request (Cuti): Full CRUD + Approve
            $allPerms('LeaveRequest'),
            ['Approve:LeaveRequest'],
            // Payroll Master (Earning/Deduction): View Only
            $viewPerms('Earning'),
            $viewPerms('Deduction'),
            // Tax Setting: No Access (excluded)
            // Slip Gaji (Payroll): Create/Read Draft + submit + check
            $viewPerms('Payroll'),
            ['Create:Payroll', 'Check:Payroll', 'Submit:Payroll', 'Calculate:Payroll'],
            // View company info
            $viewPerms('Company'),
        );
        $hrManager->syncPermissions(array_unique($hrManagerPerms));

        // ════════════════════════════════════════════════════════════
        //  SPEC SECTION 1.3 — Level 3: Operational Finance
        //  Modules: Payroll ✓+ApproveFin, Payroll Master ✓,
        //           Tax Setting ✓, User/Attendance/Leave View Only,
        //           Shift ✗
        // ════════════════════════════════════════════════════════════
        $financeManager = Role::firstOrCreate(['name' => 'finance_manager', 'guard_name' => 'web']);
        $financeManagerPerms = array_merge(
            // Slip Gaji (Payroll): Full CRUD + Approve + MarkPaid
            $allPerms('Payroll'),
            ['Approve:Payroll', 'MarkPaid:Payroll'],
            // Payroll Master (Earning/Deduction): Full CRUD
            $allPerms('Earning'),
            $allPerms('Deduction'),
            $allPerms('Designation'),
            // Tax Setting: Full CRUD
            $allPerms('TaxSetting'),
            // User & Employee Designation: View Only
            $viewPerms('User'),
            $viewPerms('EmployeeDesignation'),
            // Attendance (Presensi): View Only
            $viewPerms('Attendance'),
            // Leave Request (Cuti): View Only
            $viewPerms('LeaveRequest'),
            // Shift Kerja & Cabang: No Access (excluded)
            // View company/branch
            $viewPerms('Company'),
            $viewPerms('Branch'),
        );
        $financeManager->syncPermissions(array_unique($financeManagerPerms));

        // ════════════════════════════════════════════════════════════
        //  SPEC SECTION 1.4 — Level 4: Board of Directors
        //  Executive View & Final Authorization.
        //  View Only on all modules + Final Approve/Paid on payroll.
        // ════════════════════════════════════════════════════════════
        $director = Role::firstOrCreate(['name' => 'director', 'guard_name' => 'web']);
        $directorPerms = array_merge(
            // View Only on ALL resources
            array_merge(...array_map($viewPerms, $resources)),
            // Payroll: Final Approve + Mark Paid (executive authorization)
            ['Approve:Payroll', 'MarkPaid:Payroll'],
            // Leave Request: Final Approve (executive authorization)
            ['Approve:LeaveRequest'],
        );
        $director->syncPermissions(array_unique($directorPerms));

        // ════════════════════════════════════════════════════════════
        //  Level 5: HRD & IT Staff — operational HRIS management
        //  Modules: User ✓, Attendance ✓, Shift ✓, Branch ✓,
        //           LeaveRequest ✓, Payroll Create/Read/Calculate,
        //           Payroll Master (E/D/Designation) ✓, Tax Setting ✓
        // ════════════════════════════════════════════════════════════
        $hrdIt = Role::firstOrCreate(['name' => 'hrd_it', 'guard_name' => 'web']);
        $hrdItPerms = array_merge(
            // Full CRUD on payroll master configuration
            $allPerms('Designation'),
            $allPerms('Shift'),
            $allPerms('Earning'),
            $allPerms('Deduction'),
            $allPerms('TaxSetting'),
            $allPerms('EmployeeDesignation'),
            // Payroll: view + calculate + submit
            $viewPerms('Payroll'),
            ['Calculate:Payroll', 'Submit:Payroll', 'Create:Payroll'],
            // Attendance & Leave: full CRUD
            $allPerms('Attendance'),
            $allPerms('LeaveRequest'),
            // Users: full CRUD
            $allPerms('User'),
            // View company/branch
            $viewPerms('Company'),
            $viewPerms('Branch'),
        );
        $hrdIt->syncPermissions(array_unique($hrdItPerms));

        // ════════════════════════════════════════════════════════════
        //  Level 6: Employee — mobile app access only, no admin panel
        // ════════════════════════════════════════════════════════════
        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        // No admin permissions — ESS access via API/mobile app only
    }
}
