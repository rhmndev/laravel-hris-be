<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollCalculationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class PayrollController extends Controller implements HasMiddleware
{
    protected $payrollService;

    public static function middleware(): array
    {
        return ['auth:sanctum'];
    }

    public function __construct(PayrollCalculationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Get all payrolls (Admin only)
     */
    public function index(Request $request)
    {
        $query = Payroll::query();

        // Filter by month/year if provided
        if ($request->has('month')) {
            $query->where('month', $request->month);
        }
        if ($request->has('year')) {
            $query->where('year', $request->year);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $payrolls = $query->with(['user', 'details'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(20);

        return response()->json([
            'message' => 'Daftar payroll',
            'data' => $payrolls
        ]);
    }

    /**
     * Get specific payroll
     */
    public function show(Payroll $payroll)
    {
        return response()->json([
            'message' => 'Detail payroll',
            'data' => $payroll->load(['user', 'details', 'approvals', 'calculatedBy', 'approvedBy'])
        ]);
    }

    /**
     * Create/Calculate new payroll
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        try {
            $payroll = $this->payrollService->calculatePayroll(
                $validated['user_id'],
                $validated['month'],
                $validated['year']
            );

            return response()->json([
                'message' => 'Payroll berhasil dihitung',
                'data' => $payroll->load('details')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghitung payroll: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update payroll
     */
    public function update(Request $request, Payroll $payroll)
    {
        // Only allow updates to draft status
        if ($payroll->status !== 'draft') {
            return response()->json([
                'message' => 'Hanya payroll dengan status draft yang dapat diubah'
            ], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|in:transfer,cash,check',
        ]);

        $payroll->update($validated);

        return response()->json([
            'message' => 'Payroll berhasil diupdate',
            'data' => $payroll
        ]);
    }

    /**
     * Delete payroll
     */
    public function destroy(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return response()->json([
                'message' => 'Hanya payroll dengan status draft yang dapat dihapus'
            ], 403);
        }

        $payroll->delete();

        return response()->json([
            'message' => 'Payroll berhasil dihapus'
        ]);
    }

    /**
     * Batch calculate payroll for all employees
     */
    public function batchCalculate(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $users = User::where('employee_status', 'aktif')
            ->where('employee_id', '!=', null)
            ->get();

        $results = [];
        $errors = [];

        foreach ($users as $user) {
            try {
                $payroll = $this->payrollService->calculatePayroll(
                    $user->id,
                    $validated['month'],
                    $validated['year']
                );
                $results[] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'status' => 'success',
                    'payroll_id' => $payroll->id
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'message' => 'Batch calculate selesai',
            'total' => count($users),
            'success' => count($results),
            'failed' => count($errors),
            'results' => $results,
            'errors' => $errors
        ]);
    }

    /**
     * Approve payroll
     */
    public function approve(Request $request, Payroll $payroll)
    {
        if ($payroll->status !== 'calculated') {
            return response()->json([
                'message' => 'Hanya payroll dengan status calculated yang dapat diapprove'
            ], 403);
        }

        $payroll->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id
        ]);

        return response()->json([
            'message' => 'Payroll berhasil diapprove',
            'data' => $payroll
        ]);
    }

    /**
     * Reject payroll
     */
    public function reject(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10'
        ]);

        if (!in_array($payroll->status, ['calculated', 'approved'])) {
            return response()->json([
                'message' => 'Payroll tidak dapat ditolak dengan status saat ini'
            ], 403);
        }

        $payroll->update([
            'status' => 'rejected',
            'notes' => ($payroll->notes ? $payroll->notes . ' | ' : '') . 'Ditolak: ' . $validated['reason']
        ]);

        return response()->json([
            'message' => 'Payroll berhasil ditolak',
            'data' => $payroll
        ]);
    }

    /**
     * Mark payroll as paid
     */
    public function markPaid(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:transfer,cash,check',
            'payment_reference' => 'nullable|string'
        ]);

        if ($payroll->status !== 'approved') {
            return response()->json([
                'message' => 'Hanya payroll dengan status approved yang dapat ditandai sebagai paid'
            ], 403);
        }

        $payroll->update([
            'status' => 'paid',
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?? null
        ]);

        return response()->json([
            'message' => 'Payroll berhasil ditandai sebagai paid',
            'data' => $payroll
        ]);
    }
}
