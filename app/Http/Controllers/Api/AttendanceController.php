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

        $company = Company::getCompany();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company information not found',
            ], 404);
        }

        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $company->latitude,
            $company->longitude
        );

        if ($distance > $company->radius) {
            return response()->json([
                'success' => false,
                'message' => 'You are outside the allowed check-in radius. Distance: ' . round($distance, 2) . ' meters',
            ], 422);
        }

        $photoPath = $request->file('photo')->store('attendances/'.$today->format('Y/m'), 'public');

        $now = Carbon::now();
        $workStartTime = Carbon::createFromTimeString($company->work_start_time);
        $status = $now->greaterThan($workStartTime->setDate($now->year, $now->month, $now->day)) ? 'late' : 'on_time';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => $today,
            // 'distance_meters' => round($distance, 2),
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

    // public function checkOut(CheckOutRequest $request): JsonResponse
    // {

    // }

    public function today()
    {

    }

    public function history()
    {

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
