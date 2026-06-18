<?php

namespace Database\Seeders;

use App\Models\TaxSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxSettings = [
            ['year' => 2024, 'pph21_rate' => 5.00, 'bpjs_kesehatan_rate' => 4.00, 'bpjs_ketenagakerjaan_rate' => 0.24, 'jht_rate' => 3.97, 'non_taxable_income' => 0],
            ['year' => 2025, 'pph21_rate' => 5.00, 'bpjs_kesehatan_rate' => 4.00, 'bpjs_ketenagakerjaan_rate' => 0.24, 'jht_rate' => 3.97, 'non_taxable_income' => 0],
            ['year' => 2026, 'pph21_rate' => 5.00, 'bpjs_kesehatan_rate' => 4.00, 'bpjs_ketenagakerjaan_rate' => 0.24, 'jht_rate' => 3.97, 'non_taxable_income' => 0],
        ];

        foreach ($taxSettings as $setting) {
            TaxSetting::updateOrCreate(
                ['company_id' => 1, 'year' => $setting['year']],
                array_merge($setting, [
                    'company_id' => 1,
                    'created_by' => 1,
                ])
            );
        }
    }
}
