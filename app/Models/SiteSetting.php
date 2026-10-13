<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Resolve a public URL for a configured image key with a default asset fallback.
     */
    public static function imageUrl(string $key, string $defaultAssetPath): string
    {
        $value = self::get($key);

        if (! $value) {
            return asset($defaultAssetPath);
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (Storage::disk('public')->exists($value)) {
            return Storage::disk('public')->url($value);
        }

        if (is_file(public_path($value))) {
            return asset($value);
        }

        return asset($value);
    }

    public const DEFAULT_THEME = 'emerald';

    /**
     * Preset palettes available in the theme manager.
     *
     * @return array<string, array{name: string, description: string, colors: array<string, string>}>
     */
    public static function themePresets(): array
    {
        return [
            'emerald' => [
                'name' => 'Emerald & Warm Sand',
                'description' => 'Signature luxury highland style with deep forest emerald, warm sand, and champagne gold.',
                'colors' => [
                    'primary' => '#1d3c34',
                    'primary_hover' => '#254d43',
                    'primary_dark' => '#102b25',
                    'primary_light' => '#dfeee4',
                    'secondary' => '#d9cab3',
                    'secondary_light' => '#e7ddca',
                    'accent' => '#c5a059',
                    'accent_dark' => '#9b6c17',
                    'accent_light' => '#f9ecd0',
                    'site_bg' => '#f6f1e8',
                    'card_bg' => '#fdfaf5',
                    'text_dark' => '#1d3c34',
                    'text_muted' => '#587165',
                ],
            ],
            'sapphire' => [
                'name' => 'Sapphire & Platinum',
                'description' => 'Maritime coastal and high-rise prestige featuring deep ocean sapphire and crisp platinum slate.',
                'colors' => [
                    'primary' => '#1e3a5f',
                    'primary_hover' => '#2b5282',
                    'primary_dark' => '#0f1f33',
                    'primary_light' => '#dbeafe',
                    'secondary' => '#cbd5e1',
                    'secondary_light' => '#e2e8f0',
                    'accent' => '#0284c7',
                    'accent_dark' => '#0369a1',
                    'accent_light' => '#e0f2fe',
                    'site_bg' => '#f8fafc',
                    'card_bg' => '#ffffff',
                    'text_dark' => '#0f172a',
                    'text_muted' => '#64748b',
                ],
            ],
            'obsidian' => [
                'name' => 'Obsidian & Champagne Gold',
                'description' => 'Ultra-luxury modern minimalism with onyx obsidian, warm metallic amber, and pearl surfaces.',
                'colors' => [
                    'primary' => '#18181b',
                    'primary_hover' => '#27272a',
                    'primary_dark' => '#09090b',
                    'primary_light' => '#f4f4f5',
                    'secondary' => '#d4d4d8',
                    'secondary_light' => '#e4e4e7',
                    'accent' => '#d97706',
                    'accent_dark' => '#b45309',
                    'accent_light' => '#fef3c7',
                    'site_bg' => '#fafaf9',
                    'card_bg' => '#ffffff',
                    'text_dark' => '#18181b',
                    'text_muted' => '#71717a',
                ],
            ],
            'burgundy' => [
                'name' => 'Bordeaux Burgundy & Pearl',
                'description' => 'Noble European estate aesthetic with deep royal wine burgundy, champagne sand, and pearl.',
                'colors' => [
                    'primary' => '#4c1d24',
                    'primary_hover' => '#632530',
                    'primary_dark' => '#2d0f14',
                    'primary_light' => '#fae8eb',
                    'secondary' => '#dfcfc7',
                    'secondary_light' => '#ede3de',
                    'accent' => '#c99738',
                    'accent_dark' => '#8e6319',
                    'accent_light' => '#fdf3dc',
                    'site_bg' => '#faf6f4',
                    'card_bg' => '#ffffff',
                    'text_dark' => '#2d0f14',
                    'text_muted' => '#786064',
                ],
            ],
            'olive' => [
                'name' => 'Forest Olive & Earth Bronze',
                'description' => 'Biophilic organic architecture inspired by lush nature, olive groves, and earth bronze warmth.',
                'colors' => [
                    'primary' => '#2d3a24',
                    'primary_hover' => '#3e5032',
                    'primary_dark' => '#1b2316',
                    'primary_light' => '#e6ede0',
                    'secondary' => '#d6cebf',
                    'secondary_light' => '#e6decb',
                    'accent' => '#c28438',
                    'accent_dark' => '#966020',
                    'accent_light' => '#fbeecc',
                    'site_bg' => '#f7f6f0',
                    'card_bg' => '#fffef9',
                    'text_dark' => '#1b2316',
                    'text_muted' => '#5c6853',
                ],
            ],
            'navy' => [
                'name' => 'Midnight Navy & Imperial Amber',
                'description' => 'Executive metropolitan prestige with deep nocturnal navy, slate structure, and warm amber gold.',
                'colors' => [
                    'primary' => '#0f172a',
                    'primary_hover' => '#1e293b',
                    'primary_dark' => '#020617',
                    'primary_light' => '#e2e8f0',
                    'secondary' => '#cbd5e1',
                    'secondary_light' => '#e2e8f0',
                    'accent' => '#f59e0b',
                    'accent_dark' => '#d97706',
                    'accent_light' => '#fef3c7',
                    'site_bg' => '#f8fafc',
                    'card_bg' => '#ffffff',
                    'text_dark' => '#0f172a',
                    'text_muted' => '#475569',
                ],
            ],
            'terracotta' => [
                'name' => 'Terracotta & Desert Rose',
                'description' => 'Sun-kissed Mediterranean villa feeling with warm clay terracotta, desert sand, and bronze glow.',
                'colors' => [
                    'primary' => '#873426',
                    'primary_hover' => '#a34131',
                    'primary_dark' => '#541e15',
                    'primary_light' => '#fceee9',
                    'secondary' => '#e0d3c7',
                    'secondary_light' => '#eee4db',
                    'accent' => '#d4883b',
                    'accent_dark' => '#a6601b',
                    'accent_light' => '#faebd9',
                    'site_bg' => '#faf7f4',
                    'card_bg' => '#fffdfb',
                    'text_dark' => '#541e15',
                    'text_muted' => '#7d5c56',
                ],
            ],
            'oak' => [
                'name' => 'Royal Oak Wood',
                'description' => 'Rich natural timber architecture with deep warm oak wood, polished walnut tones, and brushed brass accents.',
                'colors' => [
                    'primary' => '#5c3a21',
                    'primary_hover' => '#73492a',
                    'primary_dark' => '#3d2514',
                    'primary_light' => '#f5ede4',
                    'secondary' => '#dfd3c3',
                    'secondary_light' => '#eee6dc',
                    'accent' => '#c2883f',
                    'accent_dark' => '#966324',
                    'accent_light' => '#fbf1e2',
                    'site_bg' => '#f7f2ea',
                    'card_bg' => '#fffdfa',
                    'text_dark' => '#3d2514',
                    'text_muted' => '#786252',
                ],
            ],
            'cream' => [
                'name' => 'Soft Cream & Linen',
                'description' => 'Light minimalist aesthetic with delicate warm cream surfaces, soft biscuit borders, and gentle gold warmth.',
                'colors' => [
                    'primary' => '#7c5e43',
                    'primary_hover' => '#926f50',
                    'primary_dark' => '#563e2a',
                    'primary_light' => '#f9f5ee',
                    'secondary' => '#e6dcce',
                    'secondary_light' => '#f2ede4',
                    'accent' => '#c59d5f',
                    'accent_dark' => '#9e7a40',
                    'accent_light' => '#fcf6ec',
                    'site_bg' => '#faf7f2',
                    'card_bg' => '#ffffff',
                    'text_dark' => '#3d2e22',
                    'text_muted' => '#877464',
                ],
            ],
        ];
    }

    /**
     * Get default theme color palette.
     *
     * @return array<string, string>
     */
    public static function defaultThemeColors(): array
    {
        return self::themePresets()[self::DEFAULT_THEME]['colors'];
    }

    /**
     * Fallback theme colors when database is unavailable.
     *
     * @return array<string, string>
     */
    public static function fallbackTheme(): array
    {
        $defaults = self::defaultThemeColors();

        return array_merge($defaults, [
            'preset' => self::DEFAULT_THEME,
            'primary_rgb' => '29, 60, 52',
            'primary_dark_rgb' => '16, 43, 37',
            'primary_light_rgb' => '223, 238, 228',
            'secondary_rgb' => '217, 202, 179',
            'accent_rgb' => '197, 160, 89',
            'accent_light_rgb' => '249, 236, 208',
        ]);
    }

    /**
     * Resolve the active theme colors, applying configured overrides over presets.
     *
     * @return array<string, string>
     */
    public static function currentTheme(): array
    {
        $preset = self::get('theme_preset', self::DEFAULT_THEME);
        $presets = self::themePresets();
        $presetDefaults = $presets[$preset]['colors'] ?? $presets[self::DEFAULT_THEME]['colors'];

        $primary = self::get('theme_primary_color', $presetDefaults['primary']);
        $primaryHover = self::get('theme_primary_hover', $presetDefaults['primary_hover']);
        $primaryDark = self::get('theme_primary_dark', $presetDefaults['primary_dark']);
        $primaryLight = self::get('theme_primary_light', $presetDefaults['primary_light']);
        $secondary = self::get('theme_secondary_color', $presetDefaults['secondary']);
        $secondaryLight = self::get('theme_secondary_light', $presetDefaults['secondary_light']);
        $accent = self::get('theme_accent_color', $presetDefaults['accent']);
        $accentDark = self::get('theme_accent_dark', $presetDefaults['accent_dark']);
        $accentLight = self::get('theme_accent_light', $presetDefaults['accent_light']);
        $siteBg = self::get('theme_site_bg', $presetDefaults['site_bg']);
        $cardBg = self::get('theme_card_bg', $presetDefaults['card_bg']);
        $textDark = self::get('theme_text_dark', $presetDefaults['text_dark']);
        $textMuted = self::get('theme_text_muted', $presetDefaults['text_muted']);

        return [
            'preset' => $preset,
            'primary' => $primary,
            'primary_hover' => $primaryHover,
            'primary_dark' => $primaryDark,
            'primary_light' => $primaryLight,
            'secondary' => $secondary,
            'secondary_light' => $secondaryLight,
            'accent' => $accent,
            'accent_dark' => $accentDark,
            'accent_light' => $accentLight,
            'site_bg' => $siteBg,
            'card_bg' => $cardBg,
            'text_dark' => $textDark,
            'text_muted' => $textMuted,
            'primary_rgb' => self::hexToRgb($primary, '29, 60, 52'),
            'primary_dark_rgb' => self::hexToRgb($primaryDark, '16, 43, 37'),
            'primary_light_rgb' => self::hexToRgb($primaryLight, '223, 238, 228'),
            'secondary_rgb' => self::hexToRgb($secondary, '217, 202, 179'),
            'accent_rgb' => self::hexToRgb($accent, '197, 160, 89'),
            'accent_light_rgb' => self::hexToRgb($accentLight, '249, 236, 208'),
        ];
    }

    /**
     * Convert HEX color (#1d3c34) to comma-separated RGB values (29, 60, 52).
     */
    public static function hexToRgb(?string $hex, string $fallback = '29, 60, 52'): string
    {
        if (! $hex) {
            return $fallback;
        }

        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return $fallback;
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r}, {$g}, {$b}";
    }
}
