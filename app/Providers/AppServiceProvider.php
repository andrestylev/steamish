<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        // Admin gate — demo user only until seeders provide a proper admin role
        Gate::define('admin', function ($user) {
            return $user->email === 'demo@steamish.test';
        });
    }
}
