<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasUuids;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_locked',
        'updated_by',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    /**
     * Get a setting value by key.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value by key.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int|null  $userId
     * @return bool
     */
    public static function set($key, $value, $userId = null)
    {
        $setting = static::where('key', $key)->first();

        if ($setting && $setting->is_locked) {
            return false;
        }

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'updated_by' => $userId ?? auth()->id(),
            ]
        );
    }

    /**
     * Cast the value based on type.
     *
     * @param  mixed  $value
     * @param  string  $type
     * @return mixed
     */
    protected static function castValue($value, $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
    }
}
