<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported application locales.
     *
     * @var array<string, array{name: string, native: string, flag: string, code: string}>
     */
    public const SUPPORTED_LOCALES = [
        'en' => [
            'code' => 'en',
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇬🇧',
            'short' => 'EN',
        ],
        'am' => [
            'code' => 'am',
            'name' => 'Amharic',
            'native' => 'አማርኛ',
            'flag' => '🇪🇹',
            'short' => 'አማ',
        ],
        'om' => [
            'code' => 'om',
            'name' => 'Afan Oromo',
            'native' => 'Afaan Oromoo',
            'flag' => '🇪🇹',
            'short' => 'OM',
        ],
    ];

    /**
     * Handle an incoming request and set the active locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale')
            ?? $request->cookie('app_locale')
            ?? config('app.locale', 'en');

        if (! array_key_exists($locale, self::SUPPORTED_LOCALES)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

        view()->share('currentLocale', $locale);
        view()->share('supportedLocales', self::SUPPORTED_LOCALES);

        return $next($request);
    }
}
