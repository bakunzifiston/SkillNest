<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl !== '') {
            URL::forceRootUrl($appUrl);

            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }

        $siteLogoUrl = null;
        if (Schema::hasTable('settings')) {
            $logoPath = Setting::get(Setting::KEY_SITE_LOGO);
            $siteLogoUrl = $logoPath ? url('course-image/' . ltrim($logoPath, '/')) : null;
        }
        \Illuminate\Support\Facades\View::share('siteLogoUrl', $siteLogoUrl);
    }
}
