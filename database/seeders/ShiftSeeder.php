<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Shift Pagi',
                'clock_in_time' => '07:00',
                'clock_out_time' => '16:00',
                'late_tolerance_minutes' => 10,
                'early_clock_in_time' => '06:30',
                'allow_clock_out_till' => '16:30',
                'self_clocking' => true,
            ],
            [
                'name' => 'Shift Siang',
                'clock_in_time' => '13:00',
                'clock_out_time' => '22:00',
                'late_tolerance_minutes' => 10,
                'early_clock_in_time' => '12:30',
                'allow_clock_out_till' => '22:30',
                'self_clocking' => true,
            ],
            [
                'name' => 'Shift Malam',
                'clock_in_time' => '21:00',
                'clock_out_time' => '06:00',
                'late_tolerance_minutes' => 15,
                'early_clock_in_time' => '20:30',
                'allow_clock_out_till' => '06:30',
                'self_clocking' => true,
            ],
            [
                'name' => 'Regular 9-5',
                'clock_in_time' => '09:00',
                'clock_out_time' => '17:00',
                'late_tolerance_minutes' => 5,
                'early_clock_in_time' => '08:45',
                'allow_clock_out_till' => '17:15',
                'self_clocking' => false,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create([
                'company_id' => 1,
                ...$shift
            ]);
        }
    }
}
