<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\EmployeeDesignation;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;

class PayrollCalculationService
{
    public function calculatePayroll(int $userId, int $month, int $year): Payroll
    {
        // Resolve employee data upfront
        $employee = User::with('branch')->find($userId);
        $companyId = $employee?->branch?->company_id ?? 1;

        // Get Employee Salary Info BEFORE creating the record
        // (the payrolls table has NOT NULL columns that must be filled on INSERT)
        $employeeDesignation = $this->getActiveDesignation($userId);
        if (!$employeeDesignation) {
            throw new \Exception("Karyawan belum memiliki jabatan aktif");
        }

        // 1. Get Attendance Data
        $attendanceData = $this->getAttendanceData($userId, $month, $year);

        // Create payroll with all required NOT NULL fields populated
        $payroll = Payroll::firstOrCreate(
            ['user_id' => $userId, 'month' => $month, 'year' => $year],
            array_merge($attendanceData, [
                'status' => 'draft',
                'company_id' => $companyId,
                'basic_salary' => $employeeDesignation->basic_salary,
                'total_earnings' => 0,
                'total_deductions' => 0,
                'gross_salary' => 0,
                'net_salary' => 0,
            ])
        );

        // Ensure fields are up-to-date (for existing records)
        $payroll->update($attendanceData);
        $payroll->basic_salary = $employeeDesignation->basic_salary;

        // 2. Calculate Earnings
        $earnings = $this->calculateEarnings($payroll, $employeeDesignation);
        $payroll->total_earnings = $earnings['total'];

        // 3. Calculate Deductions (termasuk tax)
        $grossSalary = $payroll->basic_salary + $payroll->total_earnings;
        $deductions = $this->calculateDeductions($payroll, $grossSalary);
        $payroll->total_deductions = $deductions['total'];

        // 4. Calculate Final Salary
        $payroll->gross_salary = $grossSalary;
        $payroll->net_salary = $payroll->gross_salary - $payroll->total_deductions;

        // 5. Store Detail Components
        $this->storePayrollDetails($payroll, $earnings['details'], $deductions['details']);

        // 6. Update status
        $payroll->status = 'calculated';
        $payroll->calculated_at = now();
        $payroll->calculated_by = Auth::check() ? Auth::user()->id : 1;
        $payroll->save();

        return $payroll;
    }

    private function getAttendanceData(int $userId, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get attendance records
        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        // Count working days (weekdays only)
        $totalWorkingDays = $this->countWorkingDays($startDate, $endDate);

        // Count attendance statuses
        // Attendance table uses 'on_time' and 'late' enum values.
        // Having a record = present; no record on a working day = absent.
        $presentDays = $attendances->count(); // All records = present (on_time + late)
        $lateDays = $attendances->where('status', 'late')->count();
        $absentDays = max(0, $totalWorkingDays - $presentDays);

        // Get leave data (cuti_tahunan = paid, cuti_sakit = unpaid by default)
        $leaves = LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere([
                      ['start_date', '<=', $startDate],
                      ['end_date', '>=', $endDate]
                  ]);
            })
            ->get();

        $paidLeaveDays = $leaves->where('type', 'cuti_tahunan')->sum('total_days');
        $unpaidLeaveDays = $leaves->where('type', 'cuti_sakit')->sum('total_days');
        $sickLeaveDays = $leaves->where('type', 'cuti_sakit')->sum('total_days');

        return [
            'total_working_days' => $totalWorkingDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'paid_leave_days' => $paidLeaveDays,
            'unpaid_leave_days' => $unpaidLeaveDays,
            'sick_leave_days' => $sickLeaveDays,
        ];
    }

    private function calculateEarnings($payroll, $employeeDesignation): array
    {
        $details = [];
        $total = 0;

        // Basic Salary already in $payroll->basic_salary

        // Allowances
        if ($employeeDesignation->allowances > 0) {
            $details[] = [
                'component_id' => null,
                'component_name' => 'Tunjangan',
                'amount' => $employeeDesignation->allowances,
                'component_type' => 'earning'
            ];
            $total += $employeeDesignation->allowances;
        }

        // Bonus / Insentif could be added here based on KPI or custom rules
        // For now, we'll skip as it requires additional configuration

        return [
            'total' => $total,
            'details' => $details
        ];
    }

    private function calculateDeductions($payroll, $grossSalary): array
    {
        $details = [];
        $total = 0;

        // Tax Calculation
        $taxService = new TaxCalculationService();
        $taxes = $taxService->calculate($payroll, $grossSalary);

        foreach ($taxes as $taxName => $taxAmount) {
            if ($taxAmount > 0) {
                $details[] = [
                    'component_id' => null,
                    'component_name' => $taxName,
                    'amount' => $taxAmount,
                    'component_type' => 'deduction'
                ];
                $total += $taxAmount;
            }
        }

        // Get fixed/percentage deductions (non-statutory only)
        // Statutory items (tax, social_security) are already handled by TaxCalculationService above
        $deductions = \App\Models\Deduction::where('company_id', $payroll->company_id)
            ->where('is_active', true)
            ->whereNotIn('type', ['tax', 'social_security'])
            ->get();

        foreach ($deductions as $deduction) {
            if ($deduction->is_fixed) {
                $amount = $deduction->amount ?? 0;
            } else {
                $amount = ($grossSalary * ($deduction->percentage ?? 0)) / 100;
            }

            if ($amount > 0) {
                $details[] = [
                    'component_id' => $deduction->id,
                    'component_name' => $deduction->name,
                    'amount' => $amount,
                    'component_type' => 'deduction'
                ];
                $total += $amount;
            }
        }

        return [
            'total' => $total,
            'details' => $details
        ];
    }

    private function storePayrollDetails($payroll, $earningDetails, $deductionDetails)
    {
        // Clear existing details
        $payroll->details()->delete();

        // Combine all details
        $allDetails = array_merge($earningDetails, $deductionDetails);

        // Store details
        foreach ($allDetails as $detail) {
            PayrollDetail::create([
                'payroll_id' => $payroll->id,
                'component_type' => $detail['component_type'],
                'component_name' => $detail['component_name'],
                'amount' => $detail['amount'],
                'component_id' => $detail['component_id'] ?? null,
                'description' => $detail['description'] ?? null,
            ]);
        }
    }

    private function countWorkingDays($startDate, $endDate): int
    {
        $count = 0;
        $date = $startDate->copy();

        while ($date <= $endDate) {
            // Count weekdays (1 = Monday, 5 = Friday)
            if ($date->weekday() < 5) {
                $count++;
            }
            $date->addDay();
        }

        return $count;
    }

    private function getActiveDesignation($userId)
    {
        return EmployeeDesignation::where('user_id', $userId)
            ->where('is_active', true)
            ->with('designation', 'shift')
            ->first();
    }
}
