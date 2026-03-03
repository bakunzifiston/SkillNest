<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
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
        $siteLogoUrl = null;
        if (Schema::hasTable('settings')) {
            $logoPath = Setting::get(Setting::KEY_SITE_LOGO);
            $siteLogoUrl = $logoPath ? url('course-image/' . ltrim($logoPath, '/')) : null;
        }
        \Illuminate\Support\Facades\View::share('siteLogoUrl', $siteLogoUrl);
    }
}
