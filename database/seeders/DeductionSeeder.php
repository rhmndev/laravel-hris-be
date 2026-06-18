<?php

namespace Database\Seeders;

use App\Models\Deduction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * IMPORTANT: This seeder ONLY manages non-statutory, company-specific deductions
     * (loans, advances, cooperative fees, etc.).
     *
     * Statutory deductions (PPh 21, BPJS Kesehatan, BPJS Ketenagakerjaan, JHT) are
     * configured exclusively in the Tax Settings module (TaxSettingSeeder) and
     * calculated by TaxCalculationService. They must NOT exist in the deductions
     * table to avoid double-deduction.
     */
    public function run(): void
    {
        // Step 1: Deactivate any existing statutory deduction records
        // These should NOT be in the deductions table — they belong in tax_settings only.
        DB::table('deductions')
            ->where('company_id', 1)
            ->whereIn('type', ['tax', 'social_security'])
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        // Step 2: Seed non-statutory deductions only
        $deductions = [
            [
                'name' => 'Cicilan Pinjaman',
                'type' => 'loan',
                'description' => 'Cicilan pinjaman karyawan (nominal tetap, diinput manual)',
                'is_fixed' => true,
                'amount' => 0,
                'percentage' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Uang Muka Gaji',
                'type' => 'advance',
                'description' => 'Potongan uang muka gaji (nominal tetap, diinput manual)',
                'is_fixed' => true,
                'amount' => 0,
                'percentage' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Iuran Koperasi',
                'type' => 'other',
                'description' => 'Iuran koperasi karyawan — 1% dari gaji kotor',
                'is_fixed' => false,
                'amount' => null,
                'percentage' => 1.00,
                'is_active' => true,
            ],
        ];

        foreach ($deductions as $deduction) {
            Deduction::updateOrCreate(
                ['company_id' => 1, 'name' => $deduction['name']],
                array_merge($deduction, [
                    'company_id' => 1,
                    'created_by' => 1,
                ])
            );
        }
    }
}
