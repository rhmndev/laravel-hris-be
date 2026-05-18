<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Http\Requests\StoreLeaveRequest;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    private const ANNUAL_LEAVE_MAX_DAYS = 12;
    private const STATUS_PENDING = 'pending';

    public function index(Request $request): JsonResponse
    {
        $leaveRequests = LeaveRequest::query()
            ->where('user_id', $request->user()->id)
            ->latest('start_date')
            ->latest('id')
            ->get()
            ->map(fn (LeaveRequest $leaveRequest) => $this->toResponse($leaveRequest));

            return response()->json([
                'success' => true,
                'data' => $leaveRequests,
                'message' => 'Daftar permintaan cuti berhasil diambil'
            ], 200);
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $user = $request->user();
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->startOfDay();
        $requestedDay = $startDate->diffInDays($endDate) + 1;
        $today = Carbon::now()->startOfDay();

    //     if ($startDate->lt($today)) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Tanggal mulai cuti tidak boleh sebelum hari ini.'
    //     ], 422);
    // }

        if (LeaveRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [self::STATUS_PENDING, 'approved'])
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->exists()) {
                return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki permintaan cuti yang tumpang dengan tanggal yang diajukan.'
            ], 422);
        }

        if (Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('attendance_date', '>=', $startDate)
            ->whereDate('attendance_date', '<=', $endDate)
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki absensi yang tumpang dengan tanggal yang diajukan.'
            ], 422);
        }

        if ((string) $request->string('type') === LeaveRequest::TYPE_ANNUAL) {
            $useDays = $this->usedAnnualLeaveDays($user->id, $startDate->year);

            if (($useDays + $requestedDay) > self::ANNUAL_LEAVE_MAX_DAYS) {
                $remaining = max(0, self::ANNUAL_LEAVE_MAX_DAYS - $useDays);

                return response()->json([
                    'success' => false,
                    'message' => 'Sisa cuti tahun hanya ' . $remaining . ' hari untuk tahun ' . $startDate->year
                ], 422);
            }
        }

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments'.now()->format('Y/m'), 'public');
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'type' => $request->input('type'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $requestedDay,
            'reason' => $request->input('reason'),
            'attachment' => $attachmentPath,
            'status' => self::STATUS_PENDING
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan cuti berhasil diajukan',
            'data' => $this->toResponse($leaveRequest)
        ], 200);
    }

    public function destroy(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        if ((int) $leaveRequest->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus permintaan cuti ini'
            ], 404);
        }

        if ($leaveRequest->status !== self::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya permintaan cuti dengan status SEDANG PENDING yang dapat dihapus!'
            ], 422);
        }

        if ($leaveRequest->attachment) {
            Storage::disk('public')->delete($leaveRequest->attachment);
        }

        $leaveRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permintaan cuti berhasil dihapus'
        ], 200);
    }

    private function toResponse(LeaveRequest $leaveRequest): array
    {

        $startDate = $this->normalizeDate($leaveRequest->start_date);
        $endDate = $this->normalizeDate($leaveRequest->end_date);
        $reviewedAt = $this->normalizeDate($leaveRequest->reviewed_at);
        $createdAt = $this->normalizeDate($leaveRequest->created_at);

        $startYear = (int) $startDate?->year;

        $remainingAnnualDays = $startYear ? max(0, self::ANNUAL_LEAVE_MAX_DAYS - $this->usedAnnualLeaveDays($leaveRequest->user_id, $startYear))
        : null;

        return [
            'id' => $leaveRequest->id,
            'type' => $leaveRequest->type,
            'start_date' => $startDate?->toDateString(),
            'end_date' => $endDate?->toDateString(),
            'total_days' => $leaveRequest->total_days,
            'reason' => $leaveRequest->reason,
            'attachment_url' => $leaveRequest->attachment ? asset('storage/' . $leaveRequest->attachment) : null,
            'status' => $leaveRequest->status,
            'reviewed_by_name' => $leaveRequest->reviewer?->name,
            'reviewed_at' => $reviewedAt?->toDateString(),
            'review_notes' => $leaveRequest->review_notes,
            'annual_remaining_days' => $remainingAnnualDays,
            'annual_quota_days' => self::ANNUAL_LEAVE_MAX_DAYS,
            'created_at' => $createdAt?->toDateTimeString()
        ];
    }


    private function approvedAnnualLeaveDays(int $userId, int $year, ?int $excludeRequestId = null): int
    {
        $query = LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', LeaveRequest::TYPE_ANNUAL)
            ->where('status', 'approved')
            ->whereYear('start_date', $year);

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return (int) $query->sum('total_days');
    }

    private function usedAnnualLeaveDays(int $userId, int $year, ?int $excludeRequestId = null): int
    {
        $query = LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', LeaveRequest::TYPE_ANNUAL)
            ->whereIn('status', ['pending', 'approved'])
            ->whereYear('start_date', $year);

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return (int) $query->sum('total_days');
    }


    private function normalizeDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value);
    }
}
