<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'sort_order',
    ];

    /**
     * Retrieve a single setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Retrieve all settings in a group, ordered by sort_order.
     * Returns a collection keyed by 'key'.
     */
    public static function getGroup(string $group): \Illuminate\Support\Collection
    {
        return static::where('group', $group)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('key');
    }

    /**
     * Retrieve all settings grouped by their group name.
     */
    public static function getAllGrouped(): \Illuminate\Support\Collection
    {
        return static::orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');
    }
}
