<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'radius',
        'work_start_time',
        'work_end_time',
    ];

    // Define casts for the attributes
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'radius' => 'integer',
            'work_start_time' => 'datetime:H:i',
            'work_end_time' => 'datetime:H:i',
        ];
    }

    // Helper method to get the company (assuming there's only one)
    public static function getCompany(): ?self
    {
        return self::first();
    }
}
