<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxSetting extends Model
{
    protected $fillable = [
        'company_id',
        'year',
        'pph21_rate',
        'bpjs_kesehatan_rate',
        'bpjs_ketenagakerjaan_rate',
        'jht_rate',
        'non_taxable_income',
        'created_by'
    ];

    protected $casts = [
        'pph21_rate' => 'decimal:2',
        'bpjs_kesehatan_rate' => 'decimal:2',
        'bpjs_ketenagakerjaan_rate' => 'decimal:2',
        'jht_rate' => 'decimal:2',
        'non_taxable_income' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
