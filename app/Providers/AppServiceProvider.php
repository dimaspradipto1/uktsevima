<?php

namespace App\Providers;

use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        Carbon::setLocale('id');

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // View Composer untuk Header: sediakan notifikasi kontekstual sesuai role pengguna
        View::composer('layouts.dashboard.header', function ($view) {
            $user = auth()->user();
            $notifications = NotificationService::getNotificationsForUser($user);
            $view->with('headerNotifications', $notifications);
        });
    }
}
