<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\PayrollApproval;
use App\Models\PayrollHistory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PayrollApprovalService
{
    /**
     * Submit payroll for approval.
     * Creates a Level 1 (HR Check) approval record.
     */
    public function submitForApproval(Payroll $payroll): Payroll
    {
        if (!in_array($payroll->status, ['calculated'])) {
            throw new \Exception("Payroll harus berstatus 'Dihitung' untuk diajukan.");
        }

        $payroll->update(['status' => 'submitted']);

        // Create Level 1 approval (HR Check) as pending
        PayrollApproval::firstOrCreate(
            ['payroll_id' => $payroll->id, 'approval_level' => 1],
            [
                'approver_id' => null, // Will be assigned when checked
                'status' => 'pending',
            ]
        );

        $this->recordHistory($payroll, 'submitted', 'Payroll diajukan untuk persetujuan.');

        return $payroll->fresh();
    }

    /**
     * Check (verify) payroll — Level 1 approval (HR).
     * Marks Level 1 as approved and creates Level 2 (Finance) approval.
     */
    public function checkPayroll(Payroll $payroll, User $checker, string $remarks = ''): Payroll
    {
        if ($payroll->status !== 'submitted') {
            throw new \Exception("Payroll harus berstatus 'Diajukan' untuk diperiksa.");
        }

        // Approve Level 1
        $level1 = $payroll->approvals()->where('approval_level', 1)->first();
        if ($level1) {
            $level1->update([
                'approver_id' => $checker->id,
                'status' => 'approved',
                'remarks' => $remarks,
                'approved_at' => now(),
            ]);
        }

        // Create Level 2 approval (Finance Approval) as pending
        PayrollApproval::firstOrCreate(
            ['payroll_id' => $payroll->id, 'approval_level' => 2],
            [
                'approver_id' => null,
                'status' => 'pending',
            ]
        );

        $payroll->update(['status' => 'checked']);

        $this->recordHistory($payroll, 'checked', "Diperiksa oleh {$checker->name}. {$remarks}");

        return $payroll->fresh();
    }

    /**
     * Approve payroll — Level 2 approval (Finance/Director).
     * Marks Level 2 as approved and sets payroll status to 'approved'.
     */
    public function approvePayroll(Payroll $payroll, User $approver, string $remarks = ''): Payroll
    {
        if ($payroll->status !== 'checked') {
            throw new \Exception("Payroll harus berstatus 'Diperiksa' untuk disetujui.");
        }

        // Approve Level 2
        $level2 = $payroll->approvals()->where('approval_level', 2)->first();
        if ($level2) {
            $level2->update([
                'approver_id' => $approver->id,
                'status' => 'approved',
                'remarks' => $remarks,
                'approved_at' => now(),
            ]);
        }

        $payroll->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->recordHistory($payroll, 'approved', "Disetujui oleh {$approver->name}. {$remarks}");

        return $payroll->fresh();
    }

    /**
     * Reject payroll at any approval stage.
     */
    public function rejectPayroll(Payroll $payroll, User $rejector, string $remarks = ''): Payroll
    {
        if (!in_array($payroll->status, ['submitted', 'checked'])) {
            throw new \Exception("Payroll hanya bisa ditolak jika berstatus 'Diajukan' atau 'Diperiksa'.");
        }

        // Reject the current pending approval
        $pendingApproval = $payroll->approvals()
            ->where('status', 'pending')
            ->first();

        if ($pendingApproval) {
            $pendingApproval->update([
                'approver_id' => $rejector->id,
                'status' => 'rejected',
                'remarks' => $remarks,
                'approved_at' => now(),
            ]);
        }

        $payroll->update(['status' => 'rejected']);

        $this->recordHistory($payroll, 'rejected', "Ditolak oleh {$rejector->name}. {$remarks}");

        return $payroll->fresh();
    }

    /**
     * Mark payroll as paid.
     * Records payment information and sets status to 'paid'.
     */
    public function markAsPaid(Payroll $payroll, User $user, string $paymentDate, string $paymentMethod, ?string $paymentReference = null): Payroll
    {
        if ($payroll->status !== 'approved') {
            throw new \Exception("Payroll harus berstatus 'Disetujui' untuk ditandai sebagai dibayar.");
        }

        $payroll->update([
            'status' => 'paid',
            'payment_date' => $paymentDate,
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);

        $this->recordHistory($payroll, 'paid', "Dibayar oleh {$user->name}. Metode: {$paymentMethod}." . ($paymentReference ? " No Rek.: {$paymentReference}" : ''));

        return $payroll->fresh();
    }

    /**
     * Spec-defined 2-stage financial approval workflow (Section 3.2).
     *
     * Stage 1 — Finance Manager: Validates and approves calculated payroll.
     *   calculated → checked (approved_finance)
     *
     * Stage 2 — Director: Final authorization for fund disbursement.
     *   checked → approved (published/paid-ready, visible to mobile API)
     *
     * This method provides the simplified interface described in the
     * technical specification while operating on the existing status system.
     */
    public function executeApproval(User $user, int $payrollId): Payroll
    {
        $payroll = Payroll::findOrFail($payrollId);

        // Stage 1: Verification and Correction by Operational Finance
        if ($user->hasRole('finance_manager') || $user->hasRole('super_admin')) {
            if ($payroll->status !== 'calculated' && $payroll->status !== 'submitted') {
                throw new \Exception('Payroll harus dikalkulasi terlebih dahulu oleh HRD/Finance.');
            }

            // If submitted, approve Level 1 approval record
            if ($payroll->status === 'submitted') {
                $level1 = $payroll->approvals()->where('approval_level', 1)->first();
                if ($level1) {
                    $level1->update([
                        'approver_id' => $user->id,
                        'status' => 'approved',
                        'remarks' => 'Finance verification',
                        'approved_at' => now(),
                    ]);
                }
            }

            // Create Level 2 approval (Director authorization) as pending
            PayrollApproval::firstOrCreate(
                ['payroll_id' => $payroll->id, 'approval_level' => 2],
                [
                    'approver_id' => null,
                    'status' => 'pending',
                ]
            );

            $payroll->update([
                'status' => 'checked', // approved_finance stage
            ]);

            $this->recordHistory($payroll, 'checked', "Diverifikasi dan disetujui oleh Finance: {$user->name}.");

            return $payroll->fresh();
        }

        // Stage 2: Absolute Authorization for Fund Disbursement by Board of Directors
        if ($user->hasRole('director')) {
            if ($payroll->status !== 'checked') {
                throw new \Exception('Payroll belum divalidasi dan disetujui oleh Finance Manager.');
            }

            // Approve Level 2 approval record
            $level2 = $payroll->approvals()->where('approval_level', 2)->first();
            if ($level2) {
                $level2->update([
                    'approver_id' => $user->id,
                    'status' => 'approved',
                    'remarks' => 'Director final authorization',
                    'approved_at' => now(),
                ]);
            }

            $payroll->update([
                'status' => 'approved', // published stage — visible to mobile API
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $this->recordHistory($payroll, 'approved', "Otorisasi final oleh Director: {$user->name}.");

            return $payroll->fresh();
        }

        throw new \Illuminate\Auth\Access\AuthorizationException('Anda tidak memiliki hak akses otorisasi payroll.');
    }

    /**
     * Record payroll status change in history.
     */
    private function recordHistory(Payroll $payroll, string $newStatus, string $description = ''): void
    {
        try {
            $lastVersion = PayrollHistory::where('payroll_id', $payroll->id)->max('version') ?? 0;

            PayrollHistory::create([
                'payroll_id' => $payroll->id,
                'version' => $lastVersion + 1,
                'changed_by' => Auth::check() ? Auth::user()->id : 1,
                'changed_at' => now(),
                'changes' => [
                    'old_status' => $payroll->getOriginal('status') ?? $payroll->status,
                    'new_status' => $newStatus,
                ],
                'reason_for_change' => $description,
            ]);
        } catch (\Exception $e) {
            // Don't fail the workflow if history recording fails
            Log::warning('Failed to record payroll history: ' . $e->getMessage());
        }
    }
}
