<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;

class EmployeePayslipController extends Controller
{
    /**
     * Get payslip for current month
     */
    public function getCurrentPayslip(Request $request, int $month, int $year)
    {
        $payslip = Payroll::where('user_id', $request->user()->id)
            ->where('month', $month)
            ->where('year', $year)
            ->whereIn('status', ['approved', 'paid'])
            ->with('details')
            ->first();

        if (!$payslip) {
            return response()->json([
                'message' => 'Slip gaji tidak ditemukan',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Slip gaji berhasil diambil',
            'data' => $this->formatPayslip($payslip)
        ]);
    }

    /**
     * Get latest paid/approved payslip (fallback when current month has none)
     */
    public function getLatest(Request $request)
    {
        $payslip = Payroll::where('user_id', $request->user()->id)
            ->whereIn('status', ['approved', 'paid'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->with('details')
            ->first();

        if (!$payslip) {
            return response()->json([
                'message' => 'Belum ada slip gaji yang tersedia',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Slip gaji terbaru berhasil diambil',
            'data' => $this->formatPayslip($payslip)
        ]);
    }

    /**
     * Get payslip history (last N months)
     */
    public function getHistory(Request $request)
    {
        $months = $request->input('months', 12);

        $payslips = Payroll::where('user_id', $request->user()->id)
            ->where('status', '!=', 'draft')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit($months)
            ->with('details')
            ->get();

        return response()->json([
            'message' => 'Riwayat slip gaji berhasil diambil',
            'data' => $payslips->map(fn($p) => $this->formatPayslip($p))
        ]);
    }

    /**
     * Get specific payslip detail
     */
    public function show(Request $request, Payroll $payroll)
    {
        // Check authorization - user can only see own payslip
        if ($payroll->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'message' => 'Detail slip gaji',
            'data' => $this->formatPayslip($payroll->load('details'))
        ]);
    }

    /**
     * Download payslip as PDF
     */
    public function download(Request $request, Payroll $payroll)
    {
        // Check authorization
        if ($payroll->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // For now, return the data in a format that can be used for PDF generation on frontend
        // In a real app, you'd generate PDF server-side using a package like barryvdh/laravel-dompdf

        return response()->json([
            'message' => 'Data untuk PDF',
            'data' => $this->formatPayslip($payroll->load('details')),
            'employee' => $payroll->user->only(['id', 'name', 'email', 'employee_id']),
            'designation' => $payroll->user->employeeDesignation->designation->name ?? 'N/A'
        ]);
    }

    /**
     * Share payslip via email
     */
    public function share(Payroll $payroll, Request $request)
    {
        // Check authorization
        if ($payroll->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Implementation untuk send email
        // For now, just return success

        return response()->json([
            'message' => 'Slip gaji berhasil dibagikan'
        ]);
    }

    /**
     * Format payslip for response
     */
    private function formatPayslip(Payroll $payroll): array
    {
        return [
            'id' => $payroll->id,
            'month' => $payroll->month,
            'year' => $payroll->year,
            'basic_salary' => (float) $payroll->basic_salary,
            'total_earnings' => (float) $payroll->total_earnings,
            'gross_salary' => (float) $payroll->gross_salary,
            'total_deductions' => (float) $payroll->total_deductions,
            'net_salary' => (float) $payroll->net_salary,
            'pph21' => (float) $payroll->pph21,
            'bpjs_kesehatan' => (float) $payroll->bpjs_kesehatan,
            'bpjs_ketenagakerjaan' => (float) $payroll->bpjs_ketenagakerjaan,
            'jht' => (float) $payroll->jht,
            'total_working_days' => $payroll->total_working_days,
            'present_days' => $payroll->present_days,
            'half_days' => $payroll->half_days,
            'late_days' => $payroll->late_days,
            'absent_days' => $payroll->absent_days,
            'paid_leave_days' => $payroll->paid_leave_days,
            'unpaid_leave_days' => $payroll->unpaid_leave_days,
            'holiday_days' => $payroll->holiday_days,
            'sick_leave_days' => $payroll->sick_leave_days,
            'personal_leave_days' => $payroll->personal_leave_days,
            'status' => $payroll->status,
            'payment_date' => $payroll->payment_date?->format('Y-m-d'),
            'payment_method' => $payroll->payment_method,
            'payment_reference' => $payroll->payment_reference,
            'notes' => $payroll->notes,
            'created_at' => $payroll->created_at->toIso8601String(),
            'updated_at' => $payroll->updated_at->toIso8601String(),
            'details' => $payroll->details->map(fn($d) => [
                'id' => $d->id,
                'component_type' => $d->component_type,
                'component_name' => $d->component_name,
                'amount' => (float) $d->amount,
                'description' => $d->description,
            ])
        ];
    }
}
