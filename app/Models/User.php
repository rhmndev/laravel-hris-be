<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;


#[Fillable(['name', 'email', 'phone' , 'password', 'employee_id', 'role', 'branch_id', 'profile_photo', 'date_of_birth', 'identity_number', 'bank_account_number', 'bank_name', 'address', 'phone_personal', 'join_date', 'employee_status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'join_date' => 'date',
        ];
    }

    public function attendances() : HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function employeeDesignation()
    {
        return $this->hasOne(EmployeeDesignation::class)->where('is_active', true);
    }

    public function payrollApprovals(): HasMany
    {
        return $this->hasMany(PayrollApproval::class, 'approver_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // public function getProfilePhotoUrlAttribute(): ?string
    // {
    //     if (!$this->profile_photo_path) {
    //         return null;
    //     }

    //     return Storage::disk('public')->url($this->profile_photo_path);
    // }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
