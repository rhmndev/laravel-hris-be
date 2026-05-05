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
        $company = Company::create([
            'name' => 'PT Dharma Control Cable Indonesia',
            'address' => 'Jl. Contoh Alamat No. 123, Cikarang, Bekasi, Jawa Barat',
        ]);

        // Create default branch
        $branch = $company->branches()->create([
            'name' => 'Head Office',
            'address' => 'Jl. Contoh Alamat No. 123, Jakarta',
            'latitude' => -6.20000000,
            'longitude' => 106.84500000,
            'radius' => 100,
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
        ]);

        // Create 10 employee users with attendance data
        $users = User::factory()
            ->count(10)
            ->state(['branch_id' => $branch->id])
            ->create();

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
                            ->state(['branch_id' => $user->branch_id])
                            ->forDate($currentDate)
                            ->create();
                    }
                }

                $currentDate->addDay();
            }
        }
    }
}
