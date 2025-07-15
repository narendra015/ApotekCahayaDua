<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use App\Models\Product;
use Carbon\Carbon;

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
        // Gunakan Bootstrap 5 untuk pagination
        Paginator::useBootstrapFive();

        // Kirim variabel notifikasi ke semua view (global)
        View::composer('*', function ($view) {
            $lowStockCount = Product::where('qty', '<=', 5)->count();
            $expiredSoonCount = Product::whereNotNull('expired_date')
                ->whereDate('expired_date', '<=', Carbon::now()->addDays(15))
                ->count();
            $totalNotification = $lowStockCount + $expiredSoonCount;

            $view->with(compact(
                'lowStockCount',
                'expiredSoonCount',
                'totalNotification'
            ));
        });
    }
}
