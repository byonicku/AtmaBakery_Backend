<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
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
        config(['app_locale', 'id']);
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        ResetPassword::createUrlUsing(function ($notifiable, $token) {
            return "http://localhost:5173/password/{$token}?email={$notifiable->getEmailForPasswordReset()}";
           });
    }
}
