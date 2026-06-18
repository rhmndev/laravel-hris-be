<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'clock_in_time',
        'clock_out_time',
        'late_tolerance_minutes',
        'early_clock_in_time',
        'allow_clock_out_till',
        'self_clocking'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
