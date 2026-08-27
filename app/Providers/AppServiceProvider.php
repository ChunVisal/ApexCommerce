<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer(['layouts.sidebar', 'layouts.scripts'], function ($view) {
            $view->with('unseenMovements', \App\Models\StockMovement::whereNull('seen_at')->count());
        });
    }
}
