<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            // Level 1 - Direktur
            ['name' => 'Direktur Utama', 'level' => 1, 'description' => 'Pimpinan tertinggi perusahaan'],
            ['name' => 'Direktur Operasional', 'level' => 1, 'description' => 'Direktur yang mengelola operasional'],

            // Level 2 - Manager
            ['name' => 'Manager SDM', 'level' => 2, 'description' => 'Manager departemen SDM'],
            ['name' => 'Manager Keuangan', 'level' => 2, 'description' => 'Manager departemen Keuangan'],
            ['name' => 'Manager Operasional', 'level' => 2, 'description' => 'Manager departemen Operasional'],
            ['name' => 'Manager IT', 'level' => 2, 'description' => 'Manager departemen IT/Sistem'],

            // Level 3 - Supervisor / Staff Senior
            ['name' => 'Supervisor Produksi', 'level' => 3, 'description' => 'Supervisor area produksi'],
            ['name' => 'Supervisor Warehouse', 'level' => 3, 'description' => 'Supervisor warehouse/gudang'],
            ['name' => 'Senior Staff HR', 'level' => 3, 'description' => 'Staff senior bidang HR'],
            ['name' => 'Senior Staff Accounting', 'level' => 3, 'description' => 'Staff senior bidang akuntansi'],

            // Level 4 - Junior Staff / Operator
            ['name' => 'Staff HR', 'level' => 4, 'description' => 'Staff biasa bidang HR'],
            ['name' => 'Staff Accounting', 'level' => 4, 'description' => 'Staff biasa bidang akuntansi'],
            ['name' => 'Operator Produksi', 'level' => 4, 'description' => 'Operator mesin produksi'],
            ['name' => 'Operator Warehouse', 'level' => 4, 'description' => 'Operator gudang'],
            ['name' => 'General Staff', 'level' => 4, 'description' => 'Staff general'],
        ];

        foreach ($designations as $designation) {
            Designation::create([
                'company_id' => 1, // Assuming company ID 1 exists
                'name' => $designation['name'],
                'level' => $designation['level'],
                'description' => $designation['description'],
                'created_by' => 1, // Assuming admin user ID 1 exists
            ]);
        }
    }
}
