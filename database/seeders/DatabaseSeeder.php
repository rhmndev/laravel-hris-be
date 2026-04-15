<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Create default company
        Company::create([
            'name' => 'PT Dharma Control Cable Indonesia',
            'address' => 'Jl.Tekno Industri kawasan Industri Jababeka VIII No.1 Blok A3, Cikarang Kota, Kec. Cikarang Utara, Kabupaten Bekasi, Jawa Barat 17530',
            'latitude' => -6.264635607653457,
            'longitude' => 107.15553996261775,
            'radius' => 300,
            'work_start_time' => '07:00:00',
            'work_end_time' => '16:15:00',
        ]);

        // Create 10 employee users with attendance data
        $users = User::factory()->count(10)->create();

        // Generate attendance data for each user for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        foreach ($users as $user) {
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                // Skip weekends (Saturday = 6, Sunday = 0)
                if ($currentDate->dayOfWeek !== Carbon::SATURDAY && $currentDate->dayOfWeek !== Carbon::SUNDAY) {
                    // 90% chance of attendance, 10% chance of absent
                    if (fake()->boolean(90)) {
                        Attendance::factory()
                            ->for($user)
                            ->forDate($currentDate)
                            ->create();
                    }
                }

                $currentDate->addDay();
            }
        }
    }
}
