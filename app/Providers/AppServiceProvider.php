<?php

namespace App\Providers;

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
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        \Illuminate\Support\Facades\Blade::directive('inr', function ($expression) {
            return "<?php echo \App\Http\Traits\Traits::formatINR($expression); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('currency', function ($expression) {
            return "<?php echo \App\Http\Traits\Traits::formatINR($expression, 2, true); ?>";
        });
    }
}
