<?php

namespace App\Providers;

use App\Models\Application;
use App\Observers\ApplicationObserver;
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
        // Register the ApplicationObserver for automatic commission creation
        Application::observe(ApplicationObserver::class);
    }
}
