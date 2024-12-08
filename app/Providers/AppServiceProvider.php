<?php

namespace App\Providers;

use App\Models\EmployeeCV;
use App\Observers\EmployeeCvObserver;
use Illuminate\Cookie\Middleware\EncryptCookies;
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
        // EmployeeCV::observe(EmployeeCvObserver::class);
        $this->app->afterResolving(EncryptCookies::class, function ($middleware) {
            $middleware->disableFor('cookie_consent');
        });
    }
}
