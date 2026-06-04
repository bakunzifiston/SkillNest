<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // Avoid http/https redirect loops when TLS terminates at a reverse proxy.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        $siteLogoUrl = null;
        if (Schema::hasTable('settings')) {
            $logoPath = Setting::get(Setting::KEY_SITE_LOGO);
            $siteLogoUrl = $logoPath ? url('course-image/' . ltrim($logoPath, '/')) : null;
        }
        \Illuminate\Support\Facades\View::share('siteLogoUrl', $siteLogoUrl);
    }
}
