<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Local memory cache for settings.
     */
    protected static array $cache = [];

    /**
     * Get a setting value.
     */
    public static function getValue(string $key, $default = null): ?string
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $setting = self::where('key', $key)->first();
        $value = $setting ? $setting->value : $default;

        self::$cache[$key] = $value;

        return $value;
    }

    /**
     * Set a setting value.
     */
    public static function setValue(string $key, ?string $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        self::$cache[$key] = $value;
    }
}
