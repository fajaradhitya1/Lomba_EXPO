<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Module;
use App\Observers\ModuleObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Tetap menggunakan putenv untuk credential Firebase
        putenv(
            'GOOGLE_APPLICATION_CREDENTIALS=' .
            env('GOOGLE_APPLICATION_CREDENTIALS')
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan Observer agar sistem sinkronisasi otomatis berjalan
        Module::observe(ModuleObserver::class);

        if (env('APP_ENV') === 'production') {
            \URL::forceScheme('https');
        }
    }
}