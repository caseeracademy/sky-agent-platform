<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Payout;
use App\Models\StudentDocument;
use App\Observers\ApplicationObserver;
use App\Observers\PayoutObserver;
use App\Observers\StudentDocumentObserver;
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
        // Register observers for automatic notifications
        Application::observe(ApplicationObserver::class);
        Payout::observe(PayoutObserver::class);
        StudentDocument::observe(StudentDocumentObserver::class);
    }
}
