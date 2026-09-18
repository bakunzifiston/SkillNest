<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        // Avoid http/https redirect loops when TLS terminates at a reverse proxy.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::share('siteLogoUrl', $this->siteLogoUrl());

        Blade::if('adminCan', function (string $module, string $action = 'view'): bool {
            return auth()->user()?->hasPermission($module, $action) ?? false;
        });

        View::composer('layouts.admin', function ($view): void {
            $user = auth()->user();

            try {
                if ($user && Schema::hasTable('roles')) {
                    $user->loadMissing(['role.permissions', 'permissions']);
                }

                $view->with('adminNavGroups', AdminAccess::navGroups($user));
            } catch (Throwable) {
                $view->with('adminNavGroups', []);
            }
        });
    }

    /**
     * Resolve the site logo without requiring a database during artisan boot
     * (composer install, package:discover, fresh clones).
     */
    private function siteLogoUrl(): ?string
    {
        try {
            if (! Schema::hasTable('settings')) {
                return null;
            }

            $logoPath = Setting::get(Setting::KEY_SITE_LOGO);

            return $logoPath ? url('course-image/'.ltrim($logoPath, '/')) : null;
        } catch (Throwable) {
            return null;
        }
    }
}
