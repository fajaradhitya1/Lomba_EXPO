<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        putenv(
            'GOOGLE_APPLICATION_CREDENTIALS=' .
            env('GOOGLE_APPLICATION_CREDENTIALS')
        );
    }

    public function boot(): void
    {
        //
    }
}