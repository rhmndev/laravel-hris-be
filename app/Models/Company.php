<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address'
    ];

    public static function getCompany(): ?self
    {
        return self::first();
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
