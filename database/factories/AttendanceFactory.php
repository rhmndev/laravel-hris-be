<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Default work hours: 09:00 - 17:00
        $workStartHour = 9;
        $workEndHour = 17;

        // Generate random check-in time (between 07:30 and 09:30)
        $checkInHour = fake()->numberBetween(7, 9);
        $checkInMinute = fake()->numberBetween(0, 59);

        // If check-in after 08:00, status is late
        $isLate = ($checkInHour > $workStartHour) || ($checkInHour === $workStartHour && $checkInMinute > 0);

        // Generate random check-out time (between 16:30 and 18:30)
        $checkOutHour = fake()->numberBetween(16, 18);
        $checkOutMinute = fake()->numberBetween(0, 59);

        // Random date within last 30 days
        $attendanceDate = fake()->dateTimeBetween('-30 days', 'now');

        // Create check-in and check-out datetime
        $checkInTime = Carbon::parse($attendanceDate)->setTime($checkInHour, $checkInMinute);
        $checkOutTime = Carbon::parse($attendanceDate)->setTime($checkOutHour, $checkOutMinute);

        // Base location (Jakarta area)
        $baseLat = -6.20000000;
        $baseLng = 106.84500000;

        return [
            'user_id' => User::factory(),
            'attendance_date' => Carbon::parse($attendanceDate)->format('Y-m-d'),
            'check_in_time' => $checkInTime,
            'check_in_latitude' => $baseLat + fake()->randomFloat(6, -0.001, 0.001),
            'check_in_longitude' => $baseLng + fake()->randomFloat(6, -0.001, 0.001),
            'check_in_photo' => 'photos/check_in_' . fake()->uuid() . '.jpg',
            'check_out_time' => $checkOutTime,
            'check_out_latitude' => $baseLat + fake()->randomFloat(6, -0.001, 0.001),
            'check_out_longitude' => $baseLng + fake()->randomFloat(6, -0.001, 0.001),
            'check_out_photo' => 'photos/check_out_' . fake()->uuid() . '.jpg',
            'status' => $isLate ? 'late' : 'on_time',
        ];
    }

    /**
     * Set status as on_time
     */
    public function onTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_time',
        ]);
    }

    /**
     * Set status as late
     */
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
        ]);
    }

    /**
     * Only check-in, no check-out yet
     */
    public function withoutCheckOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_out_time' => null,
            'check_out_latitude' => null,
            'check_out_longitude' => null,
            'check_out_photo' => null,
        ]);
    }

    /**
     * Set specific attendance date
     */
    public function forDate(Carbon|string $date): static
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $this->state(function (array $attributes) use ($date) {
            $checkInTime = $attributes['check_in_time'] ? Carbon::parse($attributes['check_in_time']) : null;
            $checkOutTime = $attributes['check_out_time'] ? Carbon::parse($attributes['check_out_time']) : null;

            return [
                'attendance_date' => $date->format('Y-m-d'),
                'check_in_time' => $checkInTime ? $date->copy()->setTime($checkInTime->hour, $checkInTime->minute) : null,
                'check_out_time' => $checkOutTime ? $date->copy()->setTime($checkOutTime->hour, $checkOutTime->minute) : null,
            ];
        });
    }
}
