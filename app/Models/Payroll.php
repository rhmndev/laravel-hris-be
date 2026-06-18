<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'month', 'year',
        'total_working_days', 'present_days', 'half_days', 'late_days',
        'absent_days', 'paid_leave_days', 'unpaid_leave_days', 'holiday_days',
        'sick_leave_days', 'personal_leave_days',
        'basic_salary', 'total_earnings', 'total_deductions',
        'gross_salary', 'net_salary',
        'pph21', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan', 'jht',
        'status', 'payment_date', 'payment_method', 'payment_reference',
        'notes', 'calculated_by', 'approved_by', 'calculated_at', 'approved_at'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'basic_salary' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'pph21' => 'decimal:2',
        'bpjs_kesehatan' => 'decimal:2',
        'bpjs_ketenagakerjaan' => 'decimal:2',
        'jht' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PayrollApproval::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(PayrollHistory::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
