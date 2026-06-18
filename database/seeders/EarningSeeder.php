<?php

namespace Database\Seeders;

use App\Models\Earning;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EarningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $earnings = [
            [
                'name' => 'Gaji Pokok',
                'type' => 'basic',
                'description' => 'Gaji pokok karyawan',
                'is_active' => true,
            ],
            [
                'name' => 'Tunjangan Kesehatan',
                'type' => 'allowance',
                'description' => 'Tunjangan kesehatan dan asuransi',
                'is_active' => true,
            ],
            [
                'name' => 'Tunjangan Transportasi',
                'type' => 'allowance',
                'description' => 'Tunjangan transportasi/bensin',
                'is_active' => true,
            ],
            [
                'name' => 'Tunjangan Makan',
                'type' => 'allowance',
                'description' => 'Tunjangan makan/uang makan',
                'is_active' => true,
            ],
            [
                'name' => 'Bonus Kinerja',
                'type' => 'bonus',
                'description' => 'Bonus bulanan berdasarkan kinerja',
                'is_active' => true,
            ],
            [
                'name' => 'Bonus Tahunan (THR)',
                'type' => 'bonus',
                'description' => 'Tunjangan Hari Raya (THR)',
                'is_active' => true,
            ],
            [
                'name' => 'Insentif Penjualan',
                'type' => 'incentive',
                'description' => 'Insentif berdasarkan target penjualan',
                'is_active' => true,
            ],
            [
                'name' => 'Insentif Produksi',
                'type' => 'incentive',
                'description' => 'Insentif berdasarkan target produksi',
                'is_active' => true,
            ],
            [
                'name' => 'Tunjangan Lembur',
                'type' => 'overtime',
                'description' => 'Tunjangan jam lembur',
                'is_active' => true,
            ],
        ];

        foreach ($earnings as $earning) {
            Earning::create([
                'company_id' => 1,
                'created_by' => 1,
                ...$earning
            ]);
        }
    }
}
