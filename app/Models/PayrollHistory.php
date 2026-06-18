<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollHistory extends Model
{
    public $timestamps = false;

    protected $table = 'payroll_history';

    protected $fillable = [
        'payroll_id',
        'version',
        'changes',
        'changed_by',
        'changed_at',
        'reason_for_change'
    ];

    protected $casts = [
        'changes' => 'array',
        'changed_at' => 'datetime',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
