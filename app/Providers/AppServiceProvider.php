<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\StockMovement;
use App\Models\FinancialMovement;

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
            $view->with('unseenStockMovements', StockMovement::whereNull('seen_at')->count());
            $view->with('unseenFinancialMovements', FinancialMovement::whereNull('seen_at')->count());
        });
    }
}
