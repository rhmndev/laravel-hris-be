<?php

namespace App\Services;

use App\Models\TaxSetting;

class TaxCalculationService
{
    public function calculate($payroll, $grossSalary): array
    {
        $year = $payroll->year;
        $companyId = $payroll->company_id;

        // Get tax settings for the year
        $taxSetting = TaxSetting::where('company_id', $companyId)
            ->where('year', $year)
            ->first();

        if (!$taxSetting) {
            // Use default rates if not configured
            $pph21Rate = 5;
            $bpjsKesehatanRate = 4.00;
            $bpjsKetenagakerjaanRate = 0.24;
            $jhtRate = 3.97;
            $nonTaxableIncome = 0;
        } else {
            $pph21Rate = $taxSetting->pph21_rate ?? 5;
            $bpjsKesehatanRate = $taxSetting->bpjs_kesehatan_rate ?? 4.00;
            $bpjsKetenagakerjaanRate = $taxSetting->bpjs_ketenagakerjaan_rate ?? 0.24;
            $jhtRate = $taxSetting->jht_rate ?? 3.97;
            $nonTaxableIncome = $taxSetting->non_taxable_income ?? 0;
        }

        $taxes = [];

        // PPh 21 Calculation
        // Simplified: Taxable = (Gross - Non-taxable income) - (BPJS + JHT deductions)
        $bpjsKesehatan = ($grossSalary * $bpjsKesehatanRate) / 100;
        $bpjsKetenagakerjaan = ($grossSalary * $bpjsKetenagakerjaanRate) / 100;
        $jht = ($grossSalary * $jhtRate) / 100;

        $totalNonSalaryDeductions = $bpjsKesehatan + $bpjsKetenagakerjaan + $jht;
        $taxableIncome = max(0, $grossSalary - $nonTaxableIncome - $totalNonSalaryDeductions);

        $pph21 = ($taxableIncome * $pph21Rate) / 100;

        $taxes['PPh 21'] = round($pph21, 2);
        $taxes['BPJS Kesehatan'] = round($bpjsKesehatan, 2);
        $taxes['BPJS Ketenagakerjaan'] = round($bpjsKetenagakerjaan, 2);
        $taxes['JHT'] = round($jht, 2);

        // Store in payroll object
        $payroll->pph21 = $taxes['PPh 21'];
        $payroll->bpjs_kesehatan = $taxes['BPJS Kesehatan'];
        $payroll->bpjs_ketenagakerjaan = $taxes['BPJS Ketenagakerjaan'];
        $payroll->jht = $taxes['JHT'];

        return $taxes;
    }
}
