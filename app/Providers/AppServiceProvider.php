<?php

namespace App\Providers;

use App\Models\SiteSetting;
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
            return [
                'name' => SiteSetting::get('site_name', 'GTP Real Estate'),
                'logo' => SiteSetting::get('site_logo', 'images/logo1.jpg'),
                'email' => SiteSetting::get('contact_email', 'gtmrealstate@gmail.com'),
                'phone' => SiteSetting::get('contact_phone', '+251 993722346'),
                'address' => SiteSetting::get('contact_address', 'Harar, Ethiopia'),
            ];
        } catch (Throwable) {
            return [
                'name' => 'GTP Real Estate',
                'logo' => 'images/logo1.jpg',
                'email' => 'gtmrealstate@gmail.com',
                'phone' => '+251 993722346',
                'address' => 'Harar, Ethiopia',
            ];
        }
    }
}
