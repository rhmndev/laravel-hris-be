<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CheckInRequest;
use App\Http\Requests\CheckOutRequest;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Company;


class AttendanceController extends Controller
{
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today();

        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $today)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked in today',
            ], 400);
        }

        $branch = $user->branch;

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Company information not found',
            ], 404);
        }

        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $branch->latitude,
            $branch->longitude
        );

        if ($distance > $branch->radius) {
            return response()->json([
                'success' => false,
                'message' => 'You are outside the allowed check-in radius. Distance: ' . round($distance, 2) . ' meters',
            ], 422);
        }

        $photoPath = $request->file('photo')->store('attendances/'.$today->format('Y/m'), 'public');

        $now = Carbon::now();
        $workStartTime = Carbon::createFromTimeString($branch->work_start_time);
        $status = $now->greaterThan($workStartTime->setDate($now->year, $now->month, $now->day)) ? 'late' : 'on_time';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'attendance_date' => $today,
            'check_in_time' => $now,
            'check_in_latitude' => $request->latitude,
            'check_in_longitude' => $request->longitude,
            'check_in_photo' => $photoPath,
            'status' => $status,
        ]);

        $message = $status === 'late' ? 'Checked in successfully, but you are late' : 'Checked in successfully';

        return response()->json([
            'success' => true,
            'data' => [
                'attendance' => $attendance,
                'distance_meters' => round($distance, 2) . ' meters',
            ] ,
            'message' => $message,
        ]);

    }

    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today();

        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu Belum Check-in Hari Ini',
            ], 400);
        }

        if ($existingAttendance->check_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu Sudah Check-out Hari Ini',
            ], 400);
        }

        $branch = $user->branch;

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Company information not found',
            ], 404);
        }

        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $branch->latitude,
            $branch->longitude
        );

        if ($distance > $branch->radius) {
            return response()->json([
                'success' => false,
                'message' => 'You are outside the allowed check-out radius. Distance: ' . round($distance, 2) . ' meters',
            ], 422);
        }

        $photoPath = $request->file('photo')->store('attendances/'.$today->format('Y/m'), 'public');

        $existingAttendance->update([
            'check_out_time' => Carbon::now(),
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
            'check_out_photo' => $photoPath,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'attendance' => $existingAttendance,
                'distance_meters' => round($distance, 2) . ' meters',
            ],
            'message' => 'Checked out successfully.',
        ]);
    }

    public function today(Request $request): JsonResponse
    {
        $attendance = Attendance::where('user_id', $request->user()->id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' =>  $attendance ? 'Today\'s attendance retrieved successfully' : 'You have not checked in today',
            ]);
    }

    public function history(Request $request): JsonResponse
    {
        $month = $request->query('month', Carbon::now()->month);
        $year = $request->query('year', Carbon::now()->year);

        $start = Carbon::createFromDate((int)$year, (int)$month, 1)->startOfDay();
        $today = Carbon::today()->startOfDay();
        $endOfMonth = $start->copy()->endOfMonth()->startOfDay();
        $isCurrentMont = ((int)$year === (int)$today->year) && ((int)$month === (int)$today->month);
        $end = $isCurrentMont ? $today : $endOfMonth;

        $attendances = Attendance::where('user_id', request()->user()->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->orderBy('attendance_date', 'desc')
            ->get();

        $attendanceMap = $attendances->keyBy(function (Attendance $attendances) {
            return $attendances->attendance_date->format('Y-m-d');
        });

        $fullMonth = [];

        for ($date = $end->copy(); $date->gte($start); $date->subDay()) {
            $key = $date->toDateString();
            $attendance = $attendanceMap->get($key);

            if ($attendance) {
                $item = $attendance->toArray();
            } else {
                $item = [
                    'attendance_date' => $key,
                    'status' => 'absent',
                    'check_in_time' => null,
                    'check_latitude' => null,
                    'check_longitude' => null,
                    'check_photo' => null,
                    'check_out_time' => null,
                    'check_out_latitude' => null,
                    'check_out_longitude' => null,
                    'check_out_photo' => null,
                ];
            }

            $fullMonth[] = $item;
        }
            return response()->json([
                'success' => true,
                'data' => $fullMonth,
                'message' => 'Attendance history retrieved successfully',
            ]);
    }

      private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
