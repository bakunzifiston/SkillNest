<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public const KEY_SITE_LOGO = 'site_logo';

    /**
     * Get a setting value (cached for 1 hour).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = 'setting.' . $key;
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $row = static::find($key);
            return $row ? $row->value : $default;
        });
    }

    /**
     * Set a setting value and clear cache.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        Cache::forget('setting.' . $key);
    }
}
