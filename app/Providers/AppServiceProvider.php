<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Resolve at render time so updated settings appear immediately.
        view()->composer('*', function ($view): void {
            $view->with('siteBrand', $this->siteBrand());
        });
    }

    /**
     * Resolve the editable brand values, falling back to defaults when the
     * settings table is not available (fresh install, migrations, tests).
     *
     * @return array<string, string>
     */
    private function siteBrand(): array
    {
        try {
            $logo = SiteSetting::get('site_logo', 'images/logo.svg');
        } catch (Throwable) {
            $logo = 'images/logo1.jpg';

            return [
                'name' => 'GTP Real Estate',
                'logo' => $logo,
                'logoUrl' => asset($logo),
                'email' => 'gtmrealstate@gmail.com',
                'phone' => '+251 993722346',
                'address' => 'Harar, Ethiopia',
            ];
        }

        return [
            'name' => SiteSetting::get('site_name', 'GTP Real Estate'),
            'logo' => $logo,
            'logoUrl' => $this->logoUrl($logo),
            'email' => SiteSetting::get('contact_email', 'gtmrealstate@gmail.com'),
            'phone' => SiteSetting::get('contact_phone', '+251 993722346'),
            'address' => SiteSetting::get('contact_address', 'Harar, Ethiopia'),
        ];
    }

    /**
     * Resolve a displayable URL for the configured logo.
     *
     * Uploaded logos live on the public storage disk (served from /storage),
     * while bundled defaults live in public/images (served from /images).
     */
    private function logoUrl(string $path): string
    {
        if ($path === '' || filter_var($path, FILTER_VALIDATE_URL)) {
            return $path !== '' ? $path : asset('images/logo.svg');
        }

        // Uploaded logos are stored on the public disk and served via /storage.
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        // Bundled default logos live in the public/images directory.
        if (is_file(public_path($path))) {
            return asset($path);
        }

        // The configured file is missing (fresh database pointing at a deleted
        // upload, for example), so fall back to the bundled default logo.
        return asset('images/logo.svg');
    }
}
