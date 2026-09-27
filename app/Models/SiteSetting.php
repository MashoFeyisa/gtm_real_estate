<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    public const CACHE_KEY = 'site_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Read a setting with a default fallback.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $settings = self::allCached();

        return $settings[$key] ?? $default;
    }

    /**
     * Persist a setting (creates or updates) and refresh the cache.
     */
    public static function set(string $key, ?string $value): void
    {
        Cache::forget(self::CACHE_KEY);

        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Read every setting as key => value, cached.
     *
     * @return array<string, string>
     */
    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => self::query()
            ->pluck('value', 'key')
            ->all());
    }
}
